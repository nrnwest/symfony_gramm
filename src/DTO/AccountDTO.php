<?php

declare(strict_types=1);

namespace App\DTO;

class AccountDTO
{
    public const NO_LOGIN = 'Для просмотра Акаунта  вы должны войти в ситему или зерегстрироваться.';
    public const VIEW_ALBUM_IS_LOGIN = 'Только зарегистрированные пользователи могу смотреть фотографии в альбомах.';
    public const ONLY_DELETE_OWNER = 'Только владелец фотографий может их удалить.';
    public const REGISTRATION_IS_ACTIVE = 'Вы уже зарегистрированны в системе';
    public const SUCCESS_VERIFIED_EMAIL = 'Ваш Email успешно подтвержден, войдите в систему';
    public const DEL_IMAGE = 'Фото удаллено';
    public const LOADED_IMAGE = 'Фото загруженно';
    public const ACCOUNT_UPDATE = 'Данные акаунта обновлены';
    public const DEL_USER = 'Пользователь удален';
}