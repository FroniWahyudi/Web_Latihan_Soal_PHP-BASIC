@echo off
REM =====================================================
REM Script Reset Database web_soal_php_basic (Windows)
REM =====================================================

REM --- Konfigurasi ---
SET "MYSQL_USER=root"
SET "MYSQL_PASS="
SET "DB_NAME=web_soal_php_basic"
SET "DUMP_FILE=C:\xampp\htdocs\Web Latihan Soal\web_soal_php_basic.sql"
SET "MYSQL_BIN=C:\xampp\mysql\bin"

REM Jika password kosong, kita omit flag -p
IF NOT DEFINED MYSQL_PASS (
    SET "PASS_ARG="
) ELSE IF "%MYSQL_PASS%"=="" (
    SET "PASS_ARG="
) ELSE (
    SET "PASS_ARG=-p%MYSQL_PASS%"
)

ECHO.
ECHO ====================================================
ECHO   RESET DATABASE %DB_NAME%
ECHO ====================================================
ECHO.

REM Periksa apakah mysql.exe ada
IF NOT EXIST "%MYSQL_BIN%\mysql.exe" (
    ECHO [ERROR] mysql.exe tidak ditemukan di %MYSQL_BIN%!
    ECHO Pastikan XAMPP terinstal dan path ke mysql.exe benar.
    PAUSE
    EXIT /B 1
)

REM Periksa apakah file dump ada
IF NOT EXIST "%DUMP_FILE%" (
    ECHO [ERROR] File dump %DUMP_FILE% tidak ditemukan!
    ECHO Pastikan file %DUMP_FILE% ada di direktori yang benar.
    PAUSE
    EXIT /B 1
)

ECHO [1] Dropping database %DB_NAME% (if exists)...
"%MYSQL_BIN%\mysql.exe" -u %MYSQL_USER% %PASS_ARG% -e "DROP DATABASE IF EXISTS %DB_NAME%;" 2> nul
IF %ERRORLEVEL% NEQ 0 (
    ECHO [ERROR] Gagal menghapus database! Periksa kredensial atau status MySQL.
    PAUSE
    EXIT /B %ERRORLEVEL%
)

ECHO [2] Creating database %DB_NAME%...
"%MYSQL_BIN%\mysql.exe" -u %MYSQL_USER% %PASS_ARG% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2> nul
IF %ERRORLEVEL% NEQ 0 (
    ECHO [ERROR] Gagal membuat database! Periksa kredensial atau status MySQL.
    PAUSE
    EXIT /B %ERRORLEVEL%
)

ECHO [3] Importing dump from %DUMP_FILE%...
"%MYSQL_BIN%\mysql.exe" -u %MYSQL_USER% %PASS_ARG% %DB_NAME% < "%DUMP_FILE%" 2> nul
IF %ERRORLEVEL% NEQ 0 (
    ECHO [ERROR] Gagal mengimpor %DUMP_FILE%! Periksa kredensial MySQL atau sintaks SQL.
    ECHO - Pastikan MySQL server berjalan (via XAMPP Control Panel).
    ECHO - Periksa apakah kata sandi MySQL benar.
    PAUSE
    EXIT /B %ERRORLEVEL%
)

ECHO.
ECHO ====================================================
ECHO   Database %DB_NAME% berhasil di-reset dan data berhasil dimasukkan!
ECHO ====================================================
ECHO.
PAUSE
EXIT /B 0