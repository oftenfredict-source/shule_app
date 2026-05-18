#!/bin/bash
# ============================================================
#  ShuleLinkWeb — Daily Automatic Backup Script
#  Email notification  : hanscode85@gmail.com
#  Google Drive upload : via rclone
#  Cron schedule       : 0 2 * * *  (Every day at 2:00 AM)
# ============================================================

# ——————————————————————————————————————————
# CONFIGURATION  (edit these to match your cPanel/hosting)
# ——————————————————————————————————————————
DATE=$(date +%Y-%m-%d_%H-%M-%S)
DAY=$(date +%Y-%m-%d)

# Paths
HOME_DIR="$HOME"
PROJECT_DIR="$HOME/public_html"          # ← change to your actual project path on cPanel
BACKUP_DIR="$HOME/backups/$DAY"

# Database
DB_NAME="shulexpert_school"
DB_USER="shulexpert_shulexpert"
DB_PASS="Javascript@3345"
DB_HOST="127.0.0.1"
DB_PORT="3306"

# Notification email
NOTIFY_EMAIL="hanscode85@gmail.com"

# Google Drive remote name (configured via rclone — see setup guide)
GDRIVE_REMOTE="gdrive"
GDRIVE_FOLDER="ShuleLinkBackups"

# ——————————————————————————————————————————
# UPLOAD FOLDERS TO BACKUP
# (These are the folders found in your project)
# ——————————————————————————————————————————
UPLOAD_FOLDERS=(
    "$PROJECT_DIR/public/userImages"
    "$PROJECT_DIR/public/Attendance"
    "$PROJECT_DIR/public/exam_papers"
    "$PROJECT_DIR/public/images"
    "$PROJECT_DIR/public/logos"
    "$PROJECT_DIR/public/signatures"
    "$PROJECT_DIR/public/stamps"
    "$PROJECT_DIR/storage/app/public/exam_papers"
    "$PROJECT_DIR/storage/app/public/expenses"
)

# ——————————————————————————————————————————
# SETUP
# ——————————————————————————————————————————
mkdir -p "$BACKUP_DIR"
LOG_FILE="$BACKUP_DIR/backup_log.txt"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "============================================"
log " ShuleLinkWeb Backup Started"
log "============================================"

# ——————————————————————————————————————————
# STEP 1: Database Backup
# ——————————————————————————————————————————
DB_FILE="$BACKUP_DIR/db_backup_$DATE.sql"
log "Starting database backup → $DB_FILE"

mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    "$DB_NAME" > "$DB_FILE" 2>>"$LOG_FILE"

if [ $? -eq 0 ]; then
    log "✅ Database backup SUCCESS ($(du -sh "$DB_FILE" | cut -f1))"
    DB_STATUS="✅ Database: SUCCESS"
else
    log "❌ Database backup FAILED"
    DB_STATUS="❌ Database: FAILED"
fi

# ——————————————————————————————————————————
# STEP 2: Upload Folders Backup
# ——————————————————————————————————————————
FILES_FILE="$BACKUP_DIR/uploads_backup_$DATE.tar.gz"
log "Starting uploads backup → $FILES_FILE"

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
        log "✅ Uploads backup SUCCESS ($(du -sh "$FILES_FILE" | cut -f1))"
        FILES_STATUS="✅ Uploads: SUCCESS ($(du -sh "$FILES_FILE" | cut -f1))"
    else
        log "❌ Uploads backup FAILED"
        FILES_STATUS="❌ Uploads: FAILED"
    fi
else
    log "⚠️  No upload folders found, skipping files backup"
    FILES_STATUS="⚠️  Uploads: No folders found"
fi

# ——————————————————————————————————————————
# STEP 3: Create Single Combined Archive
# ——————————————————————————————————————————
FULL_BACKUP="$BACKUP_DIR/full_backup_$DATE.tar.gz"
log "Creating full combined archive → $FULL_BACKUP"

FILES_TO_ARCHIVE=()
[ -f "$DB_FILE" ] && FILES_TO_ARCHIVE+=("$DB_FILE")
[ -f "$FILES_FILE" ] && FILES_TO_ARCHIVE+=("$FILES_FILE")

if [ ${#FILES_TO_ARCHIVE[@]} -gt 0 ]; then
    tar -czf "$FULL_BACKUP" "${FILES_TO_ARCHIVE[@]}" 2>>"$LOG_FILE"
    FULL_SIZE=$(du -sh "$FULL_BACKUP" | cut -f1)
    log "✅ Full archive created ($FULL_SIZE)"
    rm -f "$DB_FILE" "$FILES_FILE"
else
    log "❌ No files to archive"
fi

# ——————————————————————————————————————————
# STEP 4: Upload to Google Drive via rclone
# ——————————————————————————————————————————
GDRIVE_STATUS="⚠️  Google Drive: Not attempted"

# Find rclone binary — check multiple common locations on shared hosting
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
        log "✅ Google Drive upload SUCCESS"
        GDRIVE_STATUS="✅ Google Drive: Folder=$GDRIVE_FOLDER/$DAY"
    else
        log "❌ Google Drive upload FAILED"
        GDRIVE_STATUS="❌ Google Drive: Upload FAILED (check rclone config)"
    fi
else
    log "⚠️  rclone not found — skipping Google Drive upload"
    GDRIVE_STATUS="⚠️  Google Drive: rclone not installed. Run setup guide Step 4."
fi

# ——————————————————————————————————————————
# STEP 5: Send Email Notification
# ——————————————————————————————————————————
BACKUP_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)

EMAIL_BODY="
ShuleLinkWeb — Daily Backup Report
====================================
Date     : $DAY
Server   : $(hostname)
Duration : Completed at $(date '+%H:%M:%S')
Total Size: $BACKUP_SIZE

BACKUP STATUS:
--------------
$DB_STATUS
$FILES_STATUS
$GDRIVE_STATUS

FOLDERS BACKED UP:
$(printf '  • %s\n' "${EXISTING_FOLDERS[@]}")

NOTE:
Backup files are NOT attached to this email (to avoid size limits).
All backups are safely stored on Google Drive in folder: $GDRIVE_FOLDER/$DAY/

Access Google Drive: https://drive.google.com

— ShuleLinkWeb Backup System
"

echo "$EMAIL_BODY" | mail -s "ShuleLinkWeb Backup [$DAY] — $([ $? -eq 0 ] && echo 'SUCCESS' || echo 'CHECK REQUIRED')" "$NOTIFY_EMAIL"

if [ $? -eq 0 ]; then
    log "✅ Notification email sent to $NOTIFY_EMAIL"
else
    log "❌ Email notification FAILED"
fi

# ——————————————————————————————————————————
# STEP 6: Delete backups older than 7 days
# ——————————————————————————————————————————
find "$HOME/backups" -maxdepth 1 -type d -mtime +7 -exec rm -rf {} \; 2>>"$LOG_FILE"
log "🗑️  Old backups (>7 days) cleaned up"

log "============================================"
log " Backup Finished Successfully!"
log "============================================"
