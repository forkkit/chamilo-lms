<?php
/* For license terms, see /license.txt */

require_once __DIR__.'/../../main/inc/global.inc.php';

api_protect_admin_script();

$tool = 'justification';
$plugin = Justification::create();

$tpl = new Template($tool);
$fields = [];

$form = new FormValidator('search', 'get');
$form->addHeader('Search');
$form->addSelectAjax(
    'user_id',
    get_lang('User'),
    [],
    [
        'url' => api_get_path(WEB_AJAX_PATH).'user_manager.ajax.php?a=get_user_like',
    ]
);
$form->addButtonSearch(get_lang('Search'));
$tpl->assign('form', $form->returnForm());

$userId = isset($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

if ($form->validate()) {
    $userId = $form->getSubmitValue('user_id');
}

if ($userId) {
    $tpl->assign('user_info', api_get_user_info($userId));
    $list = $plugin->getUserJustificationList($userId);
    if ($list) {
        foreach ($list as &$item) {
            $item['justification'] = $plugin->getJustification($item['justification_document_id']);

            $item['file_path'] = Display::url(
                $item['file_path'],
                api_get_uploaded_web_url('justification', $item['id'], $item['file_path']),
                ['target' => '_blank']
            );
        }
    }
    if (empty($list)) {
        Display::addFlash(Display::return_message($plugin->get_lang('NoJustificationFound')));
    }
    $tpl->assign('list', $list);
}


$tpl->assign('user_id', $userId);
$content = $tpl->fetch('justification/view/justification_user_list.tpl');

$actionLinks = '';

$action = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

switch ($action) {
    case 'delete':
        $userJustification = $plugin->getUserJustification($id);
        if ($userJustification) {
            api_remove_uploaded_file_by_id('justification', $id, $userJustification['file_path']);

            $sql = "DELETE FROM justification_document_rel_users WHERE id = $id";
            Database::query($sql);

            Display::addFlash(Display::return_message(get_lang('Deleted')));
        }
        header('Location: '.api_get_self().'?user_id='.$userId);
        exit;
        break;
}

$actionLinks .= Display::toolbarButton(
    $plugin->get_lang('Back'),
    api_get_path(WEB_PLUGIN_PATH).'justification/list.php',
    'arrow-left',
    'primary'
);

$tpl->assign(
    'actions',
    Display::toolbarAction('toolbar', [$actionLinks])
);


$tpl->assign('content', $content);
$tpl->display_one_col_template();
