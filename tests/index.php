<?
error_reporting(E_ALL);

include __DIR__ . "/../Renderer.php";
$renderer = new \ErickSkrauch\SkinRenderer2D\Renderer();
$renderer->assignSkinFromFile(__DIR__ . "/demo_skin.png");

function resource_to_base64($resource) {
    ob_start();
        imagejpeg($resource);
        $contents = ob_get_contents();
    ob_end_clean();

    return "data:image/png;base64,".base64_encode($contents);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тестирование SkinRenderer2D</title>

    <style>
        body {
            background: #ebe8e1;
            color: #444;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <h1>Лицо | Face</h1>
    <img src="<? echo resource_to_base64($renderer->renderFace(1)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderFace(10)); ?>" />

    <h1>Перед | Front</h1>
    <img src="<? echo resource_to_base64($renderer->renderFront(1)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderFront(10)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderFront(10, 0, 0, 0)); ?>" />

    <h1>Зад | Back</h1>
    <img src="<? echo resource_to_base64($renderer->renderBack(1)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderBack(10)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderBack(10, 0, 0, 0)); ?>" />

    <h1>Комбинированный | Combined</h1>
    <img src="<? echo resource_to_base64($renderer->renderCombined(1)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderCombined(10)); ?>" />
    <img src="<? echo resource_to_base64($renderer->renderCombined(10, 0, 0, 0)); ?>" />
</body>
</html>