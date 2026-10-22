<?php
require '/var/www/html/wp-load.php';
if (! function_exists('zvij_age_cart_requires_confirmation')) { require '/tmp/zvij-age-fix/zvij-age.php'; }
function ensure($ok, $message) {if (!$ok) {throw new RuntimeException($message);}echo "PASS $message\n";}
$flowers=wc_get_products(['status'=>'publish','category'=>['cbd-cbg-vrsicki'],'limit'=>-1]);
$accessory=wc_get_product(211);
ensure(count($flowers)>=3 && $accessory instanceof WC_Product,'catalog fixtures exist');
$cart=WC()->cart;$posted=$_POST;
try {
 WC()->cart=new class {public array $items=[];public function get_cart(){return $this->items;}};
 $cases=['empty'=>[], 'accessory'=>[['data'=>$accessory,'quantity'=>1]]];
 foreach($flowers as $parent){
  $cases[$parent->get_slug()]=[['data'=>$parent,'quantity'=>1]];
  foreach($parent->get_children() as $id){$v=wc_get_product($id);if($v && $v->is_purchasable()){$cases[$parent->get_slug().'-variation']=[['data'=>$v,'quantity'=>1]];break;}}
 }
 $cases['mixed']=[['data'=>$accessory,'quantity'=>1],['data'=>$flowers[0],'quantity'=>1]];
 $cases['removed-flower']=[['data'=>$accessory,'quantity'=>1],['data'=>$flowers[0],'quantity'=>0]];
 foreach($cases as $name=>$items){
  WC()->cart->items=$items;$required=!in_array($name,['empty','accessory','removed-flower'],true);
  ensure(zvij_age_cart_requires_confirmation()===$required,"cart rule: $name");
  $_POST=[];ob_start();zvij_age_checkout_render();$html=ob_get_clean();ensure(str_contains($html,'id="zvij_age_confirm"')===$required,"checkbox: $name");
  wc_clear_notices();zvij_age_checkout_validate();ensure((wc_notice_count('error')>0)===$required,"server rejects unconfirmed flowers only: $name");
  $_POST=['zvij_age_confirm'=>'1'];wc_clear_notices();zvij_age_checkout_validate();ensure(wc_notice_count('error')===0,"confirmed validation: $name");
  $order=new WC_Order();foreach($items as $row){if($row['quantity']<=0)continue;$item=new WC_Order_Item_Product();$item->set_product($row['data']);$item->set_quantity($row['quantity']);$order->add_item($item);}
  zvij_age_checkout_save($order);ensure(($order->get_meta('_zvij_age_confirmed')!=='')===$required,"confirmation metadata scope: $name");ensure($order->get_id()===0,'test order remains unsaved');
 }
 WC()->cart->items=$cases['mixed'];$_POST=['post_data'=>'zvij_age_confirm=1'];ob_start();zvij_age_checkout_render();$html=ob_get_clean();ensure(str_contains($html,'checked=')||str_contains($html,'checked="checked"'),'AJAX fragment retains confirmation');
 foreach(['0','yes',['1']] as $invalid){ensure(!zvij_age_is_confirmed(['zvij_age_confirm'=>$invalid]),'invalid submitted value rejected');}
 $_POST=[];$order=new WC_Order();$item=new WC_Order_Item_Product();$item->set_product($flowers[0]);$item->set_quantity(1);$order->add_item($item);$blocked=false;try{zvij_age_checkout_save($order);}catch(Exception $e){$blocked=true;}ensure($blocked,'order creation independently refuses missing confirmation');
}finally{WC()->cart=$cart;$_POST=$posted;wc_clear_notices();}
echo "No orders were saved or submitted.\n";
