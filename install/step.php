<?php

if (!check_bitrix_sessid()) {
	return;
}

CAdminMessage::ShowNote('Модуль REES46 установлен');
