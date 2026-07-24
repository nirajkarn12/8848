<?php
require_once __DIR__ . '/../inc/functions.php';
ensureAuthIntegrations();

$redirect = trim((string) ($_SESSION['google_oauth_redirect'] ?? ''));
$loginUrl = BASE_URL . 'account/login.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '');

if (!isGoogleAuthEnabled()) {
    setFlash('danger', loadLang('google_auth_not_configured'));
    header('Location: ' . $loginUrl);
    exit;
}

if (!empty($_GET['error'])) {
    setFlash('danger', loadLang('google_auth_cancelled'));
    header('Location: ' . $loginUrl);
    exit;
}

$state = (string) ($_GET['state'] ?? '');
$code = trim((string) ($_GET['code'] ?? ''));
$sessionState = (string) ($_SESSION['google_oauth_state'] ?? '');

unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_redirect']);

if ($code === '' || $state === '' || $sessionState === '' || !hash_equals($sessionState, $state)) {
    setFlash('danger', loadLang('google_auth_failed'));
    header('Location: ' . $loginUrl);
    exit;
}

$profile = exchangeGoogleAuthCode($code);
if (!$profile) {
    setFlash('danger', loadLang('google_auth_failed'));
    header('Location: ' . $loginUrl);
    exit;
}

$result = loginOrRegisterGoogleUser($profile);
if (empty($result['ok'])) {
    $errorKey = $result['error'] ?? 'google_auth_failed';
    setFlash('danger', loadLang($errorKey));
    header('Location: ' . $loginUrl);
    exit;
}

setFlash('success', loadLang(!empty($result['new']) ? 'registration_successful' : 'welcome_back'));
header('Location: ' . safeAccountRedirect($redirect));
exit;
