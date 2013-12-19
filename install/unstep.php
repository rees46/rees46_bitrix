<?php

if (!check_bitrix_sessid()) {
	return;
}

CAdminMessage::ShowNote('Модуль успешно удален из системы');
