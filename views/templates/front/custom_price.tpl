{block name="product_price_and_shipping"}
    {if isset($lowest_price)}
        {assign var='custom_price' value=$lowest_price}
        {assign var='custom_price_formatted' value=Tools::displayPrice($custom_price, $currency)}

        <span class="price ">
            {if $custom_price > 0}
                {l s='From' d='Modules.Lowestcombinationprice.Shop'}
            {/if}
            {$custom_price_formatted}
        </span>
    {/if}
{/block}
