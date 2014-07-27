Как готовить обновление:

1. Вносим изменения в коде.
2. В каталоге update создаем каталог с номером новой версии и кладем в него файл description.ru, где пишем, что обновилось. В кодировке windows-1251.
3. В файле install/version.php указываем новую версию модуля в соответствии с предыдущим пунктом. А также дату.
4. В каталоге build запускаем файл build-package.php.
5. Полученный файл build/mk.rees46-X.Y.Z.zip переименовываем в X.Y.Z.zip.
6. Загружаем файл в этом разделе: http://partners.1c-bitrix.ru/personal/modules/edit_update_module.php?module=mk.rees46
