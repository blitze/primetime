<?php

/**
 *
 * @package phpBB Sitemaker [English]
 * @copyright (c) 2012 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ADD_BLOCK_EXPLAIN'							=> '*Перетащите блоки',
	'AJAX_ERROR'								=> 'Ой! Произошла ошибка при обработке вашего запроса. Пожалуйста, попробуйте еще раз.',
	'AJAX_LOADING'								=> 'Загрузка...',
	'AJAX_PROCESSING'							=> 'Работает...',

	'BACKGROUND'								=> 'Справочная информация',
	'BLOCKS'									=> 'Блоки',
	'BLOCKS_COPY_FROM'							=> 'Копировать блоки',
	'BLOCK_ACTIVE'								=> 'Активный',
	'BLOCK_CHILD_ROUTES_ONLY'					=> 'Показывать только на дочерних маршрутах',
	'BLOCK_CHILD_ROUTES_HIDE'					=> 'Скрыть на дочерних маршрутах',
	'BLOCK_CLASS'								=> 'Класс CSS',
	'BLOCK_CLASS_EXPLAIN'						=> 'Изменить внешний вид блока с классами CSS',
	'BLOCK_DESIGN'								=> 'Внешний вид',
	'BLOCK_DISPLAY_TYPE'						=> 'Отображение',
	'BLOCK_HIDE_TITLE'							=> 'Скрыть название блока?',
	'BLOCK_INACTIVE'							=> 'Неактивный',
	'BLOCK_MISSING_TEMPLATE'					=> 'Missing required block template. Please contact developer',
	'BLOCK_NOT_FOUND'							=> 'Ой! Запрошенный сервис блоков не найден',
	'BLOCK_NO_DATA'								=> 'Нет данных для отображения',
	'BLOCK_NO_ID'								=> 'Упс! Отсутствует идентификатор блока',
	'BLOCK_PERMISSION'							=> 'Permission',
	'BLOCK_PERMISSION_ALLOW'					=> 'Show to',
	'BLOCK_PERMISSION_DENY'						=> 'Hide from',
	'BLOCK_PERMISSION_EXPLAIN'					=> 'Use CTRL + click to toggle selection',
	'BLOCK_SHOW_ALWAYS'							=> 'Всегда',
	'BLOCK_STATUS'								=> 'Статус',
	'BLOCK_UPDATED'								=> 'Настройки блока успешно обновлены',

	'CANCEL'									=> 'Отмена',
	'CHILD_ROUTE'								=> 'Дочерний',
	'CHILD_ROUTE_EXPLAIN'						=> '/viewforum.php, /dir/index.php<br />/viewtopic.php?f=2&t=1<br />/articles/my-article',
	'CLEAR'										=> 'Очистить',
	'COPY'										=> 'Копия',
	'COPY_BLOCKS'								=> 'Копировать блоки?',
	'COPY_BLOCKS_CONFIRM'						=> 'Вы уверены, что хотите скопировать блоки с другой страницы?<br /><br />Это удалит все существующие блоки и их настройки для этой страницы и заменит их блоками с выбранной страницы.',

	'DEFAULT_LAYOUT_EXPLAIN'					=> 'Если установлено, то все страницы сайта, для которых вы не указали, будут наследовать блоки по умолчанию. Однако, вы можете переопределить макет по умолчанию для отдельных страниц, используя опции справа.',
	'DELETE'									=> 'Исключить',
	'DELETE_ALL_BLOCKS'							=> 'Удалить все блоки',
	'DELETE_ALL_BLOCKS_CONFIRM'					=> 'Вы уверены, что хотите удалить все блоки для этой страницы?',
	'DELETE_BLOCK'								=> 'Удалить блок',
	'DELETE_BLOCK_CONFIRM'						=> 'Вы уверены, что хотите удалить этот блок?<br /><br /><br /><strong>Примечание:</strong> Вы должны сохранить изменения разметки, чтобы сделать это постоянным.',

	'EDIT'										=> 'Правка',
	'EDIT_BLOCK'								=> 'Изменить блок',
	'EXIT_EDIT_MODE'							=> 'Выйти из режима редактирования',

	'FEED_PROBLEMS'								=> 'Возникла проблема с обработкой предоставленного rss/atom канала(-ов)',
	'FEED_URL_MISSING'							=> 'Пожалуйста, укажите по крайней мере один rss/atom канал для начала',
	'FIELD_INVALID'								=> 'Предоставленное значение для поля «%s» имеет недопустимый формат',
	'FIELD_REQUIRED'							=> '«%s» является обязательным полем',
	'FIELD_TOO_LONG'							=> 'Указанное значение для поля “%1$s” слишком длинное. Максимальное допустимое значение - %2$d.',
	'FIELD_TOO_SHORT'							=> 'Указанное значение для поля “%1$s” слишком короткое. Минимальное допустимое значение - %2$d.',

	'HIDE_ALL_BLOCKS'							=> 'Не показывать блоки на этой странице',
	'HIDE_BLOCK_POSITIONS'						=> 'Не показывать блоки для следующих позиций блоков:',

	'IMAGES'									=> 'Изображения',

	'LAYOUT'									=> 'Разбивка',
	'LAYOUT_SAVED'								=> 'Макет успешно сохранен!',
	'LAYOUT_SETTINGS'							=> 'Настройки макета',
	'LEAVE_CONFIRM'								=> 'У вас есть несохраненные изменения на этой странице. Пожалуйста, сохраните вашу работу перед перемещением',
	'LISTS'										=> 'Списки',

	'MAKE_DEFAULT_LAYOUT'						=> 'Установить макет по умолчанию',

	'OR'										=> '<strong>ИЛИ</strong>',

	'PARENT_ROUTE'								=> 'Родитель',
	'PARENT_ROUTE_EXPLAIN'						=> '/index.php<br />/viewforum.php?f=2<br />/articles',
	'PREDEFINED_CLASSES'						=> 'Предопределенные классы',

	'REDO'										=> 'Вернуть',
	'REMOVE_DEFAULT_LAYOUT'						=> 'Удалить макет по умолчанию',
	'REMOVE_STARTPAGE'							=> 'Удалить стартовую страницу',
	'ROUTE_HIDDEN_BLOCKS'						=> 'Блоки скрыты для этой страницы',
	'ROUTE_HIDDEN_POSITIONS'					=> 'Блоки скрыты для следующих позиций',
	'ROUTE_UPDATED'								=> 'Настройки страницы успешно обновлены',

	'SAVE_CHANGES'								=> 'Сохранить изменения',
	'SAVE_SETTINGS'								=> 'Сохранить настройки',
	'SELECT_ICON'								=> 'Выберите значок',
	'SETTINGS'									=> 'Настройки',
	'SETTING_TOO_BIG'							=> 'Указанное значение для установки “%1$s” слишком велико. Максимальное допустимое значение - %2$d.',
	'SETTING_TOO_LONG'							=> 'Предоставленное значение для установки “%1$s” слишком длинное. Максимальная допустимая длина %2$d.',
	'SETTING_TOO_LOW'							=> 'Предоставленное значение для установки “%1$s” слишком мало. Минимальное допустимое значение - %2$d.',
	'SETTING_TOO_SHORT'							=> 'Предоставленное значение для установки “%1$s” слишком короткое. Минимальная допустимая длина %2$d.',
	'SET_STARTPAGE'								=> 'Стартовая страница',

	'TITLES'									=> 'Заголовки',

	'UPDATE_SIMILAR'							=> 'Обновить блоки с похожими настройками',
	'UNDO'										=> 'Отменить',

	'VIEW_DEFAULT_LAYOUT'						=> 'Вид/редактирование макета по умолчанию',
	'VISIT_PAGE'								=> 'Посетить страницу',
));
