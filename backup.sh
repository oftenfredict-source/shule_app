#!/bin/bash
# ============================================================
#  ShuleLinkWeb - Daily Automatic Backup Script
#  Email notification  : hanscode85@gmail.com
#  Google Drive upload : via rclone
#  Cron schedule       : 0 2 * * *  (Every day at 2:00 AM)
# ============================================================

# ------------------------------------------------------------
# CONFIGURATION  (edit these to match your cPanel/hosting)
# ------------------------------------------------------------
DATE=$(date +%Y-%m-%d_%H-%M-%S)
DAY=$(date +%Y-%m-%d)

# Paths
HOME_DIR="/home/shulexpert"
PROJECT_DIR="/home/shulexpert/repositories/ShuleXpert"   # Laravel root (storage, etc.)
PUBLIC_HTML="/home/shulexpert/public_html"               # Document root (upload folders zipo hapa)
BACKUP_DIR="/home/shulexpert/backups/$DAY"

# Database
DB_NAME="shulexpert_school"
DB_USER="shulexpert_shulexpert"
DB_PASS="Javascript@3345"
DB_HOST="127.0.0.1"
DB_PORT="3306"

# Notification email
NOTIFY_EMAIL="hanscode85@gmail.com"

# Google Drive remote name (configured via rclone - see setup guide)
GDRIVE_REMOTE="gdrive"
GDRIVE_FOLDER="ShuleLinkBackups"

# ------------------------------------------------------------
# UPLOAD FOLDERS TO BACKUP
# public_html/ = document root (where upload files actually live on cPanel)
# storage/     = Laravel storage (inside repositories/ShuleXpert)
# ------------------------------------------------------------
UPLOAD_FOLDERS=(
    # Upload folders inside public_html (document root)
    "$PUBLIC_HTML/userImages"
    "$PUBLIC_HTML/Attendance"
    "$PUBLIC_HTML/exam_papers"
    "$PUBLIC_HTML/images"
    "$PUBLIC_HTML/logos"
    "$PUBLIC_HTML/signatures"
    "$PUBLIC_HTML/stamps"
    "$PUBLIC_HTML/img"
    "$PUBLIC_HTML/placeholder"
    # Laravel storage uploads (inside repositories)
    "$PROJECT_DIR/storage/app/public/exam_papers"
    "$PROJECT_DIR/storage/app/public/expenses"
)

# ------------------------------------------------------------
# SETUP
# ------------------------------------------------------------
mkdir -p "$BACKUP_DIR"
mkdir -p "/home/shulexpert/backups"
LOG_FILE="$BACKUP_DIR/backup_log.txt"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "============================================"
log " ShuleLinkWeb Backup Started"
log "============================================"

# ------------------------------------------------------------
# STEP 1: Database Backup
# ------------------------------------------------------------
DB_FILE="$BACKUP_DIR/db_backup_$DATE.sql"
log "Starting database backup -> $DB_FILE"

mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    "$DB_NAME" > "$DB_FILE" 2>>"$LOG_FILE"

if [ $? -eq 0 ]; then
    log "[SUCCESS] Database backup SUCCESS ($(du -sh "$DB_FILE" | cut -f1))"
    DB_STATUS="[SUCCESS] Database: SUCCESS"
else
    log "[FAILED] Database backup FAILED"
    DB_STATUS="[FAILED] Database: FAILED"
fi

# ------------------------------------------------------------
# STEP 2: Upload Folders Backup
# ------------------------------------------------------------
FILES_FILE="$BACKUP_DIR/uploads_backup_$DATE.tar.gz"
log "Starting uploads backup -> $FILES_FILE"

# Only include folders that actually exist
EXISTING_FOLDERS=()
for folder in "${UPLOAD_FOLDERS[@]}"; do
    if [ -d "$folder" ]; then
        EXISTING_FOLDERS+=("$folder")
        log "  Including: $folder"
    else
        log "  Skipping (not found): $folder"
    fi
done

if [ ${#EXISTING_FOLDERS[@]} -gt 0 ]; then
    tar -czf "$FILES_FILE" "${EXISTING_FOLDERS[@]}" 2>>"$LOG_FILE"
    if [ $? -eq 0 ]; then
        log "[SUCCESS] Uploads backup SUCCESS ($(du -sh "$FILES_FILE" | cut -f1))"
        FILES_STATUS="[SUCCESS] Uploads: SUCCESS ($(du -sh "$FILES_FILE" | cut -f1))"
    else
        log "[FAILED] Uploads backup FAILED"
        FILES_STATUS="[FAILED] Uploads: FAILED"
    fi
else
    log "[WARNING] No upload folders found, skipping files backup"
    FILES_STATUS="[WARNING] Uploads: No folders found"
fi

# ------------------------------------------------------------
# STEP 3: Create Single Combined Archive
# ------------------------------------------------------------
FULL_BACKUP="$BACKUP_DIR/full_backup_$DATE.tar.gz"
log "Creating full combined archive -> $FULL_BACKUP"

FILES_TO_ARCHIVE=()
[ -f "$DB_FILE" ] && FILES_TO_ARCHIVE+=("$DB_FILE")
[ -f "$FILES_FILE" ] && FILES_TO_ARCHIVE+=("$FILES_FILE")

if [ ${#FILES_TO_ARCHIVE[@]} -gt 0 ]; then
    tar -czf "$FULL_BACKUP" "${FILES_TO_ARCHIVE[@]}" 2>>"$LOG_FILE"
    FULL_SIZE=$(du -sh "$FULL_BACKUP" | cut -f1)
    log "[SUCCESS] Full archive created ($FULL_SIZE)"
    rm -f "$DB_FILE" "$FILES_FILE"
else
    log "[FAILED] No files to archive"
fi

# ------------------------------------------------------------
# STEP 4: Upload to Google Drive via rclone & Cleanup
# ------------------------------------------------------------
GDRIVE_STATUS="[WARNING] Google Drive: Not attempted"
SERVER_CLEANUP="[WARNING] Cleanup not attempted"

# Find rclone binary
RCLONE_BIN=""
for rclone_path in \
    "$(which rclone 2>/dev/null)" \
    "$HOME/.local/bin/rclone" \
    "$HOME/bin/rclone" \
    "/usr/bin/rclone" \
    "/usr/local/bin/rclone"; do
    if [ -x "$rclone_path" ]; then
        RCLONE_BIN="$rclone_path"
        break
    fi
done

if [ -n "$RCLONE_BIN" ]; then
    log "Found rclone at: $RCLONE_BIN"
    log "Uploading to Google Drive: $GDRIVE_REMOTE:$GDRIVE_FOLDER/$DAY/"
    "$RCLONE_BIN" copy "$BACKUP_DIR" "$GDRIVE_REMOTE:$GDRIVE_FOLDER/$DAY/" \
        --log-file="$LOG_FILE" --log-level INFO 2>>"$LOG_FILE"

    if [ $? -eq 0 ]; then
        log "[SUCCESS] Google Drive upload SUCCESS"
        GDRIVE_STATUS="[SUCCESS] Google Drive: Folder=$GDRIVE_FOLDER/$DAY"
        
        # Immediate cleanup to save shared hosting space (MB 570 only)
        BACKUP_SIZE_BEFORE=$(du -sh "$BACKUP_DIR" | cut -f1)
        rm -rf "$BACKUP_DIR"
        
        if [ $? -eq 0 ]; then
            log "[CLEANUP] Server backup files deleted ($BACKUP_SIZE_BEFORE freed) - safely on Google Drive"
            SERVER_CLEANUP="[SUCCESS] Server disk freed: $BACKUP_SIZE_BEFORE removed after upload"
        else
            log "[WARNING] Could not delete backup from server - please delete manually: $BACKUP_DIR"
            SERVER_CLEANUP="[WARNING] Manual cleanup needed: $BACKUP_DIR"
        fi
    else
        log "[FAILED] Google Drive upload FAILED - keeping backup on server for retry"
        GDRIVE_STATUS="[FAILED] Google Drive: Upload FAILED (check rclone config)"
        SERVER_CLEANUP="[WARNING] Backup kept on server (upload failed): $BACKUP_DIR"
    fi
else
    log "[WARNING] rclone not found - skipping Google Drive upload"
    GDRIVE_STATUS="[WARNING] Google Drive: rclone not installed. Run setup guide Step 4."
    SERVER_CLEANUP="[WARNING] Backup kept on server (no rclone): $BACKUP_DIR"
fi

# ------------------------------------------------------------
# STEP 5: Send Email Notification
# ------------------------------------------------------------
EMAIL_BODY="
ShuleLinkWeb - Daily Backup Report
====================================
Date      : $DAY
Server    : $(hostname)
Time      : $(date '+%H:%M:%S')
Total Size: $FULL_SIZE

BACKUP STATUS:
--------------
$DB_STATUS
$FILES_STATUS
$GDRIVE_STATUS
$SERVER_CLEANUP

FOLDERS BACKED UP:
$(printf '  * %s\n' "${EXISTING_FOLDERS[@]}")

NOTE:
[+] Backup files are uploaded to Google Drive and REMOVED from server.
[+] Server disk space is kept clean automatically.
[-] Google Drive Folder: $GDRIVE_FOLDER/$DAY/

Access Google Drive: https://drive.google.com

- ShuleLinkWeb Backup System
"

echo "$EMAIL_BODY" | mail -s "ShuleLinkWeb Backup [$DAY] - SUCCESS" "$NOTIFY_EMAIL"

if [ $? -eq 0 ]; then
    log "[SUCCESS] Notification email sent to $NOTIFY_EMAIL"
else
    log "[FAILED] Email notification FAILED"
fi

log "============================================"
log " Backup Finished! Server disk kept clean."
log "============================================"
