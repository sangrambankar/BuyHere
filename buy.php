<?php
session_start();
print'<html>
<head>
<title>Buy Products</title>
<link rel="stylesheet" type="text/css" href="stylesheet.css"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Lato" />
</head>
<body>
<div class="header"><h1>Shopping Cart Application</h1></div>
<div class="container">';
error_reporting(E_ALL);
ini_set('display_errors','On');
$categoryXmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=72&showAllDescendants=true');
$categoryXml=new SimpleXMLElement($categoryXmlstr);
header('Content-Type: text/html');
$totalValue=0;

if(!isset($_SESSION['products']) || $_SESSION['products']==null)
{
  $_SESSION['products']=array();
  $_SESSION['productId']=array();
}
if (isset($_GET['buy'])) {
  $productId=$_GET['buy'];
  $productLink='http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&productId='.$productId;
  $productXmlStr = file_get_contents($productLink);
  $productxml = new SimpleXMLElement($productXmlStr);
  $productOfferUrl=(string)$productxml->categories->category->items->product->productOffersURL;
  $productName=(string)$productxml->categories->category->items->product->name;
  $productMinprice=(string)$productxml->categories->category->items->product->minPrice;
  $productImage=(string)$productxml->categories->category->items->product->images->image[0]->sourceURL;
  $product_array=array();
  array_push($product_array,$productId);
  array_push($product_array,$productImage);
  array_push($product_array,$productName);
  array_push($product_array,$productMinprice);
  array_push($product_array,$productOfferUrl);

  if(!in_array($productId,$_SESSION['productId'])){
    $_SESSION['productId'][$productId]=$productId;
    $_SESSION['products'][$productId]=$product_array;
  }
}
elseif (isset($_GET['delete'])) {
  $deleteProductId=$_GET['delete'];
  unset($_SESSION['products'][$deleteProductId]);
  unset($_SESSION['productId'][$deleteProductId]);
}
elseif (isset($_GET['clear'])) {
  session_unset();
  $_SESSION['products']=array();
  $_SESSION['productId']=array();
}

print '<div class="left">
<div class="top">
<h2>Shopping Basket</h2>
<div class="clear">
  <form action="buy.php" method="GET">
    <input type="hidden" name="clear" value="1">
    <input type="submit" class="clearbutton" value="Clear">
  </form>
</div>
</div>';
if(!empty($_SESSION['products']))
{

print '<div class="scroll">  
<div id="output" class="results custombg left-results">';

$array =  $_SESSION['products'];
foreach ($array  as $a) {
    if($a!='')
    {
      $href='buy.php?delete='.$a[0];
      print '<div class="card left-card"> 
              <div class="">   
                <a href="'.$a[4].'"><img src="'.$a[1].'" alt="No Image Found" /></a>
              </div>
              <div class="info">
                <p>'.$a[2].'</p>
                <p class="price">'.$a[3].'$</p>
              </div>
              <div class="delete" ><a href='.$href.'><img src="http://iconshow.me/media/images/Application/Modern-Flat-style-Icons/png/32/Delete.png" /></a></div>
            </div>';
       $totalValue=$totalValue+$a[3];
    }  
  }
print  '</div>
</div>';
  
}
else{
  print '<h2 style="text-align: center;">No items in your cart</h2>';
}

print '<h2 style="margin-top:10px;" class="price">Total: '.$totalValue.'$</h2>

</div>


<div id="search">
<form action="buy.php" method="GET">
  <h2>Find products</h2>
  <div>
    <select name="category">';
    print '<option value='.$categoryXml->category['id'].'>'.(string)$categoryXml->category->name.'</option>';
    print '<optgroup label="'.(string)$categoryXml->category->name.'">';

    foreach($categoryXml->category->categories->category as $list){
      print '<option value='.$list['id'].'>'.(string)$list->name.'</option>';
      if(!empty($list->categories))
        print '<optgroup label="'.(string)$list->name.':">';
        foreach ($list->categories->category as $key){
          print '<option value='.$key['id'].'>'.(string)$key->name.'</option>';
        }
        print '</optgroup>';
    }
    print '</optgroup>
    </select>
  

  
  <input type="text" name="search" placeholder="Keywords">
  <input type="submit" class="button" value="Search">

 </div>

</form>';

if(isset($_GET['category']) && isset($_GET['search'])){
  $categoryParam=$_GET['category'];
  $searchParam=$_GET['search'];
  $keyword=str_replace(' ','+',$searchParam);
  if($keyword!=''){

    $link='http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId='.$categoryParam.'&keyword='.$keyword.'&numItems=20';
    $xmlstr = file_get_contents($link);
      print_r($xmlstr);

    $xml = new SimpleXMLElement($xmlstr);

    
    if((string)$xml->categories['returnedCategoryCount']!='0'){
      print '<div class="scroll">  
      <div id="output" class="results custombg left-results">';
      foreach($xml->categories->category->items as $item){
        foreach($item->product as $p){
          $href='buy.php?buy='.$p['id'];
          $imgsrc=(string)$p->images->image->sourceURL;
          $description = (string)$p->fullDescription;
          if($description == '') $description = "Full description is not available";
          print '<div class="card right-card"> 
              <div class="">   
                <a href="'.$href.'"><img src="'.$imgsrc.'" alt="No Image Found" /></a>
              </div>
              <div class="info-right">
                <p>'.(string)$p->name.'</p>
                <p class="price">'.(string)$p->minPrice.'$</p>
              </div>
              <div class="description"><p>'.$description.'</p></div>
            </div>';


        }
      } 
      print  '</div>
      </div>';
    }else{
        print '<h2>There is no item with '.$keyword.' keyword</h2>';
    }
  }
}
print '</div>';
?>
</body>
</html>
