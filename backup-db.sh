#!/bin/bash

LOG_FILE="/var/log/backup-db.log"
exec > "$LOG_FILE" 2>&1

# Конфигурация
BACKUP_DIR="./backups"
#BACKUP_DIR="/backups"
DB_NAME="symfony_db"
DB_USER="root"
DB_PASSWORD="password"
CONTAINER_NAME="my_first_project-database-1"  # или название твоего контейнера

# Проверяем, что контейнер запущен
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Ошибка: Контейнер $CONTAINER_NAME не запущен"
    echo "Запустите: docker compose up -d"
    exit 1
fi

# Создаём директорию для бэкапов, если её нет
mkdir -p "$BACKUP_DIR"

# Генерируем имя файла с датой и временем
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${DATE}.sql"

echo "Создаю дамп базы данных $DB_NAME..."

# Выполняем mysqldump внутри контейнера
docker exec "$CONTAINER_NAME" mysqldump \
    -u"$DB_USER" \
    -p"$DB_PASSWORD" \
    "$DB_NAME" > "$BACKUP_FILE"

# Проверяем, успешно ли создался дамп
if [ $? -eq 0 ] && [ -f "$BACKUP_FILE" ]; then
    # Сжимаем дамп для экономии места
    gzip "$BACKUP_FILE"
    echo "✅ Дамп создан: ${BACKUP_FILE}.gz"
    echo "   Размер: $(du -h "${BACKUP_FILE}.gz" | cut -f1)"
else
    echo "❌ Ошибка при создании дампа"
    rm -f "$BACKUP_FILE"
    exit 1
fi

# Удаляем бэкапы старше 7 дней
echo "Удаляю бэкапы старше 7 дней..."
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +7 -delete
echo "✅ Очистка завершена"
