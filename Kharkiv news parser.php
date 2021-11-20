<?php
$url = 'https://057.ua/rss';
$xml = file_get_contents($url);
$xml = str_replace('&amp;', '&', $xml);

$news = simplexml_load_string($xml);

$categories = [];
foreach ($news->channel->item as $newsItem) {
    $category = (string)$newsItem->category;

    if (!in_array($category, $categories, true)) {
        $categories[] = $category;
    }
}

// Filter by category
$selectedCategory = filter_input(INPUT_POST, 'selectedCategory');
$isSelectedCategoryPresent = !empty($selectedCategory);

if ($isSelectedCategoryPresent && !in_array($selectedCategory, $categories, true)) {
    die('Попытка взлома');
}

// Filter by limit
$selectedLimit = filter_input(INPUT_POST, 'selectedLimit');

$isSelectedLimitPresent = !empty($selectedLimit);

$defaultLimit = 10;

if ($isSelectedLimitPresent) {
    if (!ctype_digit($selectedLimit)) {
        $selectedLimit = $defaultLimit;
    }

    $selectedLimit = (int) $selectedLimit;

    if ($selectedLimit < 0 || $selectedLimit > 100) {
        $selectedLimit = $defaultLimit;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kharkov News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>
<body style="font-family:italic">
<form method="post">
     Лимит новостей:
    <input type="text" name="selectedLimit" value="<?php echo  $selectedLimit ?>" />
    Категория:
    <select name="selectedCategory">
        <option value="" selected>Все</option>
        <?php
        foreach ($categories as $category) {
            echo "<option>$category</option>";
        }
        ?>
    </select>
    <input type="submit">
</form>

<table>
    <tr>
        <th>Изображение</th>
        <th>Категория</th>
        <th>Заголовок</th>
        <th>Описание</th>
        <th>Опубликовано</th>
        <th>Контент</th>
    </tr>
    <?php
        $showedNewsCount = 0;
        foreach ($news->channel->item as $newsItem) {
            if ($isSelectedCategoryPresent && $selectedCategory !== (string) $newsItem->category) {
                continue;
            }

            echo '<tr>';

            echo '<td>';
            echo "<img src='" . $newsItem->enclosure['url'] . "' width='200' height='100'>";
            echo '</td>';

            echo '<td>' . $newsItem->category . '</td>';
            echo '<td>' . $newsItem->title . '</td>';
            echo '<td>' . $newsItem->description . '</td>';
            echo '<td>' . $newsItem->pubDate . '</td>';
            echo '<td>' . $newsItem->children('content', true)->encoded . '</td>';

            echo '</tr>';

            $showedNewsCount++;

            if ($isSelectedLimitPresent && $showedNewsCount === $selectedLimit) {
                break;
            }
        }
    ?>
</table>

</body>
</html>