<?php
use Slim\Collection;
use Slim\Http\Request;
use Slim\Http\Response;
use Tuupola\Middleware\CorsMiddleware;

/**
 * @var Collection $settings
 */
$settings = $app->getContainer()->get('settings');

$app->add(new CorsMiddleware(
    [
        "origin" => ["*"],
        "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
        "headers.allow" => [
            "Ref",
            "Language",
            "TermId",
            "Content-Type",
            "Authorization",
            "If-Match",
            "If-Unmodified-Since",
            "User-Agent",
            "Connection",
            "Pragma",
            "Accept",
            "Accept-Encoding",
            "Accept-Language",
            "Token",
            "OwnerId",
            "Domain",
            "Page"
        ],
        "headers.expose" => ["Etag"],
        "credentials" => true,
        "cache" => 86400,
        "error" => function ($request, $response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]
));

// jwt, oauth
$authMode = 'no_jwt';

$auth = $settings->get('auth');

if($authMode === 'jwt') {
    $app->add(new Tuupola\Middleware\JwtAuthentication([
        "path" => ["/api", "/api/auth"],
        "ignore" => [
            "/api/auth/login",
            "/api/auth/pwdres",
            "/api/test",
            "/api/channels"
        ],
        "secret" => $auth['secret'],
        "secure" => false,
        "attribute" => "token",
        // "relaxed" => ["localhost"],
        "before" => function ($request, $arguments) {
            /** @var Request $request */
            // print_r($arguments);
            $isUpdate =  $request->isDelete() || $request->isPatch() || $request->isPost() || $request->isPut();

            if($isUpdate) {
                // print_r($arguments['decoded']['user']->acl);
                // die(  'end');
                // $user = $arguments['decoded']['user'] ;
                // print_r(user);

            }

            return $request;
        },
        "after" => function (Response $response, $arguments) {
            $canGoOn = true;
            if($canGoOn) return $response;
            else return $response->withStatus(401);
        },
        "error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));

} else if($authMode === 'oauth') {
    // not implemented yet
}
