<?
error_reporting(E_ALL);

include __DIR__ . "/../Renderer.php";
$renderer = new \ErickSkrauch\SkinRenderer2D\Renderer();
$renderer->assignSkinFromFile(__DIR__ . "/demo_skin.png");

function temp_link($resource) {
    static $counter = 0;
    $counter++;

    imagepng($resource, __DIR__ . "/temp/".$counter.".png");

    return "/tests/temp/".$counter.".png";
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
    <img src="<? echo temp_link($renderer->renderFace(1)); ?>" />
    <img src="<? echo temp_link($renderer->renderFace(10)); ?>" />

    <h1>Перед | Front</h1>
    <img src="<? echo temp_link($renderer->renderFront(1)); ?>" />
    <img src="<? echo temp_link($renderer->renderFront(10)); ?>" />
    <img src="<? echo temp_link($renderer->renderFront(10, 0, 0, 0)); ?>" />

    <h1>Зад | Back</h1>
    <img src="<? echo temp_link($renderer->renderBack(1)); ?>" />
    <img src="<? echo temp_link($renderer->renderBack(10)); ?>" />
    <img src="<? echo temp_link($renderer->renderBack(10, 0, 0, 0)); ?>" />

    <h1>Комбинированный | Combined</h1>
    <img src="<? echo temp_link($renderer->renderCombined(1)); ?>" />
    <img src="<? echo temp_link($renderer->renderCombined(10)); ?>" />
    <img src="<? echo temp_link($renderer->renderCombined(10, 0, 0, 0)); ?>" />

    <h1>Деградатор | Degrade</h1>
    <img src="<? echo temp_link($renderer->degrade(false)); ?>" />
    <img src="<? echo temp_link($renderer->degrade(true)); ?>" />

    <h1>Обновлятор | Improve</h1>
    <? $renderer->assignSkinFromFile(__DIR__ . "/demo_skin_old.png"); ?>
    <img src="<? echo temp_link($renderer->improve()); ?>" />
</body>
</html>