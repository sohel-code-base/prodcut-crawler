<?php
$url = 'https://yourpetpa.com.au';
$baseUrl = 'https://yourpetpa.com.au';
$context = stream_context_create(
    array(
        "http" => array(
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);
$data = [
    ['Title', 'Description', 'Price', 'Product url', 'Image url']
];
$html = file_get_contents($url, false, $context);

preg_match_all('~<a.*?href="(.*?)".*?class="product-block__title-link">.*?</a>~', $html, $matches);

$links = array();
foreach ($matches[1] as $key => $match) {
    $link = $baseUrl.$match;
    $product = file_get_contents($link, false, $context);

    preg_match('/<h3(.*?)class="product-detail__title(.*?)>(.*?)<\/h3>/s', $product, $title);
    preg_match('/<div(.*?)class="product-price(.*?)>(.*?)<\/div>/s', $product, $price_area);
    preg_match('/<div(.*?)class="product__description_full--width(.*?)>(.*?)<\/div>/s', $product, $description_area);
    preg_match('/<div(.*?)class="product-media product-media--image(.*?)>(.*?)<\/div>/s', $product, $image_area);
    preg_match('/<img(.*?)>/s', $image_area[3], $image);
    preg_match('/data-src="(.*?)"/s', $image[1], $image_link);
    preg_match('/data-widths="(.*?)"/s', $image[1], $image_sizes);

    $img_size = trim(explode(',' ,$image_sizes[1])[count(explode(',' ,$image_sizes[1])) - 2]);
    $img_link = str_replace("{width}", $img_size, $image_link);
    $img_actual_link = substr($img_link[1], 2);

    preg_match('/<span(.*?)class="theme-money(.*?)>(.*?)<\/span>/s', $price_area[3], $price);
    preg_match('/<span(.*?)data-mce-fragment(.*?)>(.*?)<\/span>/s', $description_area[3], $description);

    $collectData = [isset($title[3]) ? $title[3] : '', isset($description[3]) ? $description[3] : '', isset($price[3]) ? $price[3] : '', $link, $img_actual_link];
    array_push($data, $collectData);


}

$fp = fopen(__DIR__.'/products.csv', 'w');
foreach ($data as $row) {
    fputcsv($fp, $row);
}

fclose($fp);
echo "Product CSV generated!";
exit();


