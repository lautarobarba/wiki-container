<?php

use BookStack\Facades\Theme;
use BookStack\Theming\ThemeEvents;
use Illuminate\Routing\Router;

require_once __DIR__ . '/ai-chat/AiChatController.php';

// Registers the endpoint used by the AI chat widget (themes/custom/common/ai-chat-widget.blade.php).
// Placed on the auth-required web route group so it gets session, CSRF and login protection for free.
Theme::listen(ThemeEvents::ROUTES_REGISTER_WEB_AUTH, function (Router $router) {
    $router->post('/ai-chat/ask', [\Theme\AiChat\AiChatController::class, 'ask'])
        ->middleware('throttle:10,1');
    $router->get('/ai-chat/history', [\Theme\AiChat\AiChatController::class, 'history']);
    $router->post('/ai-chat/clear', [\Theme\AiChat\AiChatController::class, 'clear']);
});
