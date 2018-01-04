@echo off
PUSHD %~dp0

FOR /F "eol=# delims== tokens=1,*" %%a in (config/playguard.conf) DO (
  IF NOT "%%a"=="" (
    IF NOT "%%b"=="" (
      SET playguard.%%a=%%b
    )
  )
)
FOR /F "eol=# delims== tokens=1,*" %%a in (config/password.conf) DO (
  IF NOT "%%a"=="" (
    IF NOT "%%b"=="" (
      SET playguard.password.%%a=%%b
    )
  )
)
SET command=%1
IF "%command%" NEQ "logout" (
  IF "%command%" NEQ "login" (
    ECHO "USAGE: %0 (login^|logout)"
    EXIT /B 1
  )
)
REM you may put your absolute path to CURL here if you do not have it on your global PATH variable
REM SET curl=C:\Program Files\curl\bin\curl.exe
SET curl=curl.exe
SET login=%USERNAME%
SET login=joelle
SET variable=playguard.password.%login%
FOR /F %%a IN ('ECHO %%%variable%%%') DO SET password=%%a
FOR /F "tokens=* USEBACKQ" %%a IN (`"%curl%" -k -s -u %login%:%password% -w ~%%{http_code} %playguard.url%/event.php^?cmd^=%command%^^^&src^=%playguard.source%^^^&minSec^=%playguard.minSecondsLeft%`) DO SET output=%%a
SET result=
SET code=200
FOR /F "delims=~ tokens=1,*" %%a IN ("%output%") DO (
  SET result=%%a
  SET code=%%b
)
IF "%code%" == "200" (
  ECHO Still %result% seconds left to play. Have fun.
) ELSE (
  ECHO CURL failed with status code %code% and result: %result%
  EXIT /B 1
)
POPD