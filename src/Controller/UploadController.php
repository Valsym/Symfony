<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\UploadFileType;
use Symfony\Component\Routing\Attribute\Route;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\Autowire; // Добавь этот импорт
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Imagine\Gd\Imagine as GdImagine;
// или use Imagine\Imagick\Imagine as ImagickImagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

#[Route('/upload')]
class UploadController extends AbstractController
{

    #[Route('/s3', name: 'upload_s3')]
    public function uploadS3(UploadedFile $file, #[Autowire(service: 's3_storage')] Filesystem $s3Storage): void
    {
        $stream = fopen($file->getRealPath(), 'r+');
        $s3Storage->writeStream(
            'uploads/' . $file->getClientOriginalName(),
            $stream
        );
        fclose($stream);
    }

    #[Route('/test_s3', name: 'test_s3')]
    public function testS3(#[Autowire(service: 's3_storage')] Filesystem $s3Storage)
    {
        try {
            // Попробуем вывести список объектов (бакет должен быть пуст)
            $contents = $s3Storage->listContents('', true);
            // Просто собираем пути всех файлов
            $files = [];
            foreach ($contents as $item) {
                $files[] = $item->path();
            }

            dd($files);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    #[Route('/test_s3_write', name: 'test_s3_write')]
    public function testS3Write(#[Autowire(service: 's3_storage')] Filesystem $s3Storage): Response
    {
        try {
            // Записываем тестовый файл
            $s3Storage->write('test/test.txt', 'Hello from Symfony!');

            // Читаем его
            $content = $s3Storage->read('test/test.txt');

            return new Response("Written and read: " . $content);
        } catch (\Exception $e) {
            return new Response("Error: " . $e->getMessage(), 500);
        }
    }

        #[Route(name: 'app_upload_file', methods: ['GET'])]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(UploadFileType::class);

        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(name: 'app_upload_handle', methods: ['POST'])]
    public function handleUpload(Request $request,
                                 #[Autowire(service: 's3_storage')] Filesystem $s3Storage,
                                 CacheManager $cacheManager): Response
    {
        $form = $this->createForm(UploadFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... обработка файла
            $file = $form->get('file')->getData();

            if ($file) {
                // Максимальный размер файла 5 МБ
                $maxSize = 5 * 1024 * 1024;
                if ($file->getSize() > $maxSize) {
                    throw new \Exception('Файл слишком большой!');
                }

                // Допустимые MIME-типы
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                    throw new \Exception('Недопустимый формат файла!');
                }

                try {
                    // 1. Записываем оригинал в S3
                    $originalPath = 'uploads/' . $file->getClientOriginalName();
                    $stream = fopen($file->getRealPath(), 'r+');
                    $s3Storage->writeStream($originalPath, $stream);
                    fclose($stream);

                    // 2. Создаём превью (если это изображение)
                    if (in_array($file->getMimeType(), ['image/jpeg', 'image/png'])) {
                        // Создаём превью с помощью GD или Imagick
                        $thumbnailContent = $this->generateThumbnailFromFile($file);

                        // 3. Сохраняем превью в S3
                        $thumbnailPath = 'uploads/thumbnail/' . $file->getClientOriginalName();
                        $s3Storage->write($thumbnailPath, $thumbnailContent);
                    }

                    return new Response('File uploaded to S3 successfully!');
                } catch (\Exception $e) {
                    return new Response('Error uploading to S3: ' . $e->getMessage(), 500);
                }

            }
        }

        return $this->redirectToRoute('app_upload_file');
    }

    private function generateThumbnailFromFile0(UploadedFile $file): string
    {
        $imagine = new GdImagine();
        $image = $imagine->open($file->getRealPath());

        // Изменяем размер до 200x200, ОБРЕЗАЯ лишнее (outbound)
        $image->thumbnail(new \Imagine\Image\Box(200, 200),
            \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);

        // Если хотите просто уменьшить до 200px по большей стороне (inset):
        // $image->thumbnail(new \Imagine\Image\Box(200, 200));


        // Сохраняем в строку (в память)
        $thumbnailContent = $image->get($file->guessExtension());

        return $thumbnailContent;
    }


    #[Route('/file', name: 'app_upload_file_old', methods: ['GET', 'POST'])]
    public function uploadOld(Request $request): Response
    {
        try {
            $form = $this->createForm(UploadFileType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('file')->getData();

                if ($file) {
                    // Максимальный размер файла 5 МБ
                    $maxSize = 5 * 1024 * 1024;
                    if ($file->getSize() > $maxSize) {
                        throw new \Exception('Файл слишком большой!');
                    }

                    // Допустимые MIME-типы
                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                        throw new \Exception('Недопустимый формат файла!');
                    }

                    $fileName = uniqid() . '.' . $file->guessExtension();
                    $file->move(
                        $this->getParameter('upload_directory'), // путь задан в services.yaml
                        $fileName
                    );
                }

                return new Response('File upload test: OK');
                // Здесь будем обрабатывать файл
                //return $this->redirectToRoute('upload_success');
            }

        } catch (\Exception $e) {
            //$logger->error('Force test error: ' . $e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }

        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function generateThumbnail(CacheManager $cacheManager, string $imagePath)
    {
        // Путь к оригинальному изображению
        $fullPath = $this->getParameter('upload_directory').'/'.$imagePath;

        // Генерируем превью
        $thumbnailPath = $cacheManager->getBrowserPath($imagePath, 'my_thumb');

        return $thumbnailPath;
    }

    private function generateThumbnailFromFile(UploadedFile $file): string
    {
        $imagine = new GdImagine();
        $image = $imagine->open($file->getRealPath());

        // Уменьшаем до 200px по большей стороне, сохраняя пропорции
        $image->resize(new Box(200, 200));

        $thumbnailContent = $image->get($file->guessExtension());

        return $thumbnailContent;
    }

}
