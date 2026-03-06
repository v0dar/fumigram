<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/home");
	exit;
}

$chat  = array();
$to_id = false;
$chats_history = array();
$messages  = new messages();
$user_data = array();
$conversation = array();
$c_privacy    = false;
$old_messages = false;
$config['footer'] = false;
$online = $user->onlineStatus();
$new = false;
if (!empty($_GET['uname']) && strcmp($_GET['uname'], $me['username']) != 0) {
	$uname = $_GET['uname'];
	try {
		$user->setUserByName($uname);
		$user_data = $user->getUser();
	} 
	catch (Exception $e) {}
	if (!empty($user_data)) {
		$c_privacy   = $user->chatPrivacy($user_data->user_id);
		$is_blocked  = $user->isBlocked($user_data->user_id);
		$ami_blocked = $user->isBlocked($user_data->user_id,true);

		if ($ami_blocked || $is_blocked) {
			header("Location: $site_url");
			exit;
		}

		$messages->setUserById($me['user_id']);
		$messages->limit = 30;
		$to_id     = $user_data->user_id;
		$conv_data = $messages->getMessages($to_id,false,$new,'DESC','>');
		$_SESSION['to_id'] = $user_data->user_id;
		if (!empty($conv_data)) {
			$conversation = o2array($conv_data);
			if (count($conversation) == $messages->limit) {
				$old_messages = true;
			}
			sort($conversation);
		}
	}
}

$chats = array();
$messages->setUserById($me['user_id']);
$chats_history = $messages->getChats();
if (!empty($chats_history)) {
	$chats = o2array($chats_history);
}

$context['app_name'] = 'messages';
$context['chats_history'] = $chats;
$context['c_privacy'] = $c_privacy;
$context['conversation'] = $conversation;
$context['old_messages'] = $old_messages;
$context['page_title'] = lang('messages');
$context['user_data'] = o2array($user_data);
$context['page_link'] = 'messages/'.$context['user_data']['username'];
$context['content'] = $ui->intel('messages/templates/messages/index');
