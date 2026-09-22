@echo off
setlocal EnableExtensions

set "WORDPRESS_CODE_DIR=wordpress_code"
set "PLUGIN_DIR=mps-tools"
set "BUILD_DIR=build"
set "FINAL_DIR=%BUILD_DIR%\%PLUGIN_DIR%"

echo Nettoyage ancien build...
if exist "%BUILD_DIR%" (
    rmdir /S /Q "%BUILD_DIR%"
    if errorlevel 1 exit /B 1
)

mkdir "%FINAL_DIR%"
if errorlevel 1 exit /B 1

echo Build Vite...
call npm run build-windows
if errorlevel 1 exit /B 1

echo Copie des fichiers WordPress...
xcopy "%WORDPRESS_CODE_DIR%\*" "%FINAL_DIR%\" /E /I /H /Y
if errorlevel 2 exit /B 1

echo Copie du dossier dist...
xcopy "%BUILD_DIR%\dist\*" "%FINAL_DIR%\dist\" /E /I /H /Y
if errorlevel 2 exit /B 1

echo Deplacement du manifest...
move /Y "%FINAL_DIR%\dist\.vite\manifest.json" "%FINAL_DIR%\manifest.json"
if errorlevel 1 exit /B 1

echo Creation du ZIP...
pushd "%BUILD_DIR%"
if errorlevel 1 exit /B 1

tar -a -c -f "%PLUGIN_DIR%.zip" "%PLUGIN_DIR%"
popd
if errorlevel 1 (
    popd
    exit /B 1
)

popd

echo Plugin pret : %BUILD_DIR%\%PLUGIN_DIR%.zip

endlocal