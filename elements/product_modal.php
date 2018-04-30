<?php defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
?>
<form class="store-product-modal" id="store-form-add-to-cart-modal-<?= $product->getID()?>">

    <div class="store-product-modal-info-shell">

        <a href="#" class="store-modal-exit">x</a>
        <h4 class="store-product-modal-title"><?= $product->getName()?></h4>

        <p class="store-product-modal-thumb">
            <?php
            $imgObj = $product->getImageObj();
            $ih = Core::make("helper/image");
            $thumb = $ih->getThumbnail($imgObj,560,999,false);
            ?>
            <img src="<?= $thumb->src?>">
        </p>


        <p class="store-product-modal-price"><?= $product->getFormattedPrice()?></p>
        <div class="store-product-modal-details">
            <?= $product->getDesc()?>
        </div>
        <div class="store-product-modal-options">
            <?php if (Config::get('community_store.shoppingDisabled') != 'all') { ?>
            <div class="store-product-modal-quantity form-group">
                <?php if ($product->allowQuantity()) { ?>
                    <label class="store-product-option-group-label"><?= t('Quantity') ?></label>
                    <input type="number" name="quantity" class="store-product-qty form-control" value="1" min="1" step="1" max="<?= $product->getQty()?>">
                <?php } else { ?>
                    <input type="hidden" name="quantity" class="store-product-qty form-control" value="1">
                <?php } ?>
            </div>
            <?php } ?>
            <?php
            foreach ($product->getOptions() as $option) {
            $optionItems = $option->getOptionItems();

             if (!empty($optionItems)) { ?>
                <div class="store-product-option-group form-group">
                    <label class="store-product-option-group-label"><?= $option->getName() ?></label>
                    <select class="store-product-option form-control" name="po<?= $option->getID() ?>">
                        <?php
                        foreach ($optionItems as $optionItem) {
                            if (!$optionItem->isHidden()) { ?>
                                <option value="<?= $optionItem->getID() ?>"><?= $optionItem->getName() ?></option>
                            <?php }
                            // below is an example of a radio button, comment out the <select> and <option> tags to use instead
                            //echo '<input type="radio" name="po'.$option->getID().'" value="'. $optionItem->getID(). '" />' . $optionItem->getName() . '<br />'; ?>
                        <?php } ?>
                    </select>
                </div>
            <?php }
            } ?>
        </div>
        <input type="hidden" name="pID" value="<?= $product->getID()?>">
        <?php if (Config::get('community_store.shoppingDisabled') != 'all') { ?>
        <div class="store-product-modal-buttons">
            <p><button data-add-type="modal" data-product-id="<?= $product->getID()?>" class="store-btn-add-to-cart btn btn-primary <?= ($product->isSellable() ? '' : 'hidden');?> "><?=  ($btnText ? h($btnText) : t("Add to Cart"))?></button></p>
            <p class="store-out-of-stock-label alert alert-warning <?= ($product->isSellable() ? 'hidden' : '');?>"><?= t("Out of Stock")?></p>
        </div>
        <?php } ?>
    </div>
</form>

<?php
if ($product->hasVariations()) {
    $variations = StoreProductVariation::getVariationsForProduct($product);

    $variationLookup = array();

    if (!empty($variations)) {
        foreach ($variations as $variation) {
            // returned pre-sorted
            $ids = $variation->getOptionItemIDs();
            $variationLookup[implode('_', $ids)] = $variation;
        }
    }
}
?>

<?php if ($product->hasVariations() && !empty($variationLookup)) {?>
    <script>
        $(function() {
            <?php
            $varationData = array();
            foreach($variationLookup as $key=>$variation) {
                $product->setVariation($variation);

                $imgObj = $product->getImageObj();

                if ($imgObj) {
                    $thumb = Core::make('helper/image')->getThumbnail($imgObj,560,999,false);
                }

                $varationData[$key] = array(
                'price'=>$product->getFormattedOriginalPrice(),
                'saleprice'=>$product->getFormattedSalePrice(),
                'available'=>($variation->isSellable()),
                'imageThumb'=>$thumb ? $thumb->src : '',
                'image'=>$imgObj ? $imgObj->getRelativePath() : '');

            } ?>


            $('#store-form-add-to-cart-modal-<?= $product->getID()?> select').change(function(){

                var variationdata = <?= json_encode($varationData); ?>;
                var ar = [];

                $('#store-form-add-to-cart-modal-<?= $product->getID()?> select').each(function(){
                    ar.push($(this).val());
                });

                ar.sort();

                var pli = $(this).closest('.store-product-modal');

                if (variationdata[ar.join('_')]['saleprice']) {
                    var pricing =  '<span class="store-sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                        ' <?= t('was');?> ' + '<span class="store-original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                    pli.find('.store-product-modal-price').html(pricing);

                } else {
                    pli.find('.store-product-modal-price').html(variationdata[ar.join('_')]['price']);
                }

                if (variationdata[ar.join('_')]['available']) {
                    pli.find('.store-out-of-stock-label').addClass('hidden');
                    pli.find('.store-btn-add-to-cart').removeClass('hidden');
                } else {
                    pli.find('.store-out-of-stock-label').removeClass('hidden');
                    pli.find('.store-btn-add-to-cart').addClass('hidden');
                }

                if (variationdata[ar.join('_')]['imageThumb']) {
                    var image = pli.find('.store-product-modal-thumb img');

                    if (image) {
                        image.attr('src', variationdata[ar.join('_')]['imageThumb']);

                    }
                }

            });
        });
    </script>
<?php } ?>
