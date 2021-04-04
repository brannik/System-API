@ECHO OFF
CLS
ECHO Server Restart Started %time:~0,5% %date:~1%
:SERVERLOOP
mysq_monitor.exe
ECHO Server Restart %time:~0,5% %date:~1%
ECHO.
GOTO SERVERLOOP
:END