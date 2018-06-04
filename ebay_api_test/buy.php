<?php
session_start();
?>

<html>
<head><title>Buy Products</title></head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
header('Content-Type: text');

if(isset($_GET["clear"])) {
    session_unset();
    session_destroy();
}

print 'Shopping Basket:<br/><br/>';

if(isset($_GET["delete"])) {
    unset($_SESSION['basket'][array_search($_GET["delete"],$_SESSION['basket'])]);
}

if(isset($_GET["buy"])) {
    if( isset( $_SESSION['basket'] ) ) {
        array_push($_SESSION['basket'],$_GET["buy"]);
    }else {
        $_SESSION['basket'] = array($_GET["buy"]);
    }
}

if(isset($_GET["category"]) && isset($_GET["keyword"]) && !empty($_GET["keyword"])) {
    $category = $_GET["category"];
    $keyword = $_GET["keyword"];
}
elseif(isset($_SESSION["category"]) && isset($_SESSION["keyword"]) && !empty($_SESSION["keyword"])) {
    $category = $_SESSION["category"];
    $keyword = $_SESSION["keyword"];
}

$totalBill = 0;

if( isset($_SESSION['basket']) && count($_SESSION['basket'])!=0) {

    $prodListStr="";
    foreach ($_SESSION['basket'] as $item) {
        $prodListStr = $prodListStr . '&productId=' . $item;
    }

    $xmlPurchasedProdInfoStr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=&visitorUserAgent&visitorIPAddress&trackingId=' . $prodListStr);
    $xmlPurchasedProdInfo = new SimpleXMLElement($xmlPurchasedProdInfoStr);

    print '<table border="1">';
    foreach ($xmlPurchasedProdInfo->categories->category->items->product as $product) {
        $totalBill += floatval($product->minPrice);
        print '<tr>';
        print '<td>';
        print '<img src="' . $product->images[0]->image->sourceURL . '">';
        print '</td>';
        print '<td>';
        print $product->name;
        print '</td>';
        print '<td>';
        print $product->minPrice.'$';
        print '</td>';
        print '<td>';
        print '<a href="buy.php?delete=' . $product['id'] . '">Delete</a>';
        print '</td>';
        print '</tr>';
    }
    print '</table>';

}

print '<br/>Total : '.$totalBill.'$<br/>';


print '<br/><form action="buy.php" method="GET">';
print '<input name="clear" value="1" type="hidden">';
print '<input value="Empty Basket" type="submit">';
print '</form>';



$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&showAllDescendants=true&categoryId=72');
$xml = new SimpleXMLElement($xmlstr);

print '<form action="buy.php" method="GET">';
    print '<fieldset><legend>Find products:</legend>';

    print '<label>Category: ';

    print '<select name="category">';
    print '<optgroup label="' . $xml->category->name . '">';
    foreach ($xml->category->categories->category as $mainCategory) {
        print '<optgroup label="' . $mainCategory->name . '">';

        foreach ($mainCategory->categories->category as $subCategory) {
            print '<option ';
            if(isset($category) && strval($subCategory['id'])==$category)
                print ' selected ';
            print 'value="' . $subCategory['id'] . '">' . $subCategory->name . '</option>';
        }
    }
    print '</select></label>';

    print '<label> Search keywords: ';
    if(isset($keyword))
        print '<input type="text" name="keyword" value="'.$keyword.'" /></label>';
    else
        print '<input type="text" name="keyword" /></label>';

    print '&nbsp;<input type="submit" value="Search">';

    print '</fieldset>';
print '</form>';


if(isset($category)){
    $xmlSearchResult = file_get_contents('http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&numItems=20&categoryId=' . $category . '&keyword=' . $keyword);
    $xmlResult = new SimpleXMLElement($xmlSearchResult);

    $items = $xmlResult->categories->category->items;

    if($items) {
        print '<table border="1">';
        foreach ($items->product as $product) {
            print '<tr>';
            print '<td>';
            print '<a href="buy.php?buy=' . $product['id'] . '">';
            print '<img src="' . $product->images[0]->image->sourceURL . '">';
            print '</a>';
            print '</td>';
            print '<td>';
            print $product->name;
            print '</td>';
            print '<td>';
            print $product->minPrice . '$';
            print '</td>';
            print '<td>';
            print $product->fullDescription;
            print '</td>';
            print '<td>';
            print '<a href="'.$product->productOffersURL.'">View deatils</a>';
            print '</td>';
            print '</tr>';
        }
        print '</table>';
    }

    $_SESSION["category"]=$category;
    $_SESSION["keyword"]=$keyword;

}

?>

</body>
</html>
