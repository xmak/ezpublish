<div class="view-embed">
    <div class="content-media">
    {let attribute=$object.data_map.file}
        <object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0"
                width="{$attribute.content.width}" height="{$attribute.content.height}">
        <param name="movie" value={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename)|ezurl} />
        <param name="quality" value="{$attribute.content.quality}" />
        <param name="play" value="{section show=$attribute.content.is_autoplay}true{/section}" />
        <param name="loop" value="{section show=$attribute.content.is_loop}true{/section}" />
        <embed src={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename)|ezurl}
               quality="{$attribute.content.quality}" pluginspage="{$attribute.content.pluginspage}"
               width="{$attribute.content.width}" height="{$attribute.content.height}" play="{section show=$attribute.content.is_autoplay}true{/section}"
               loop="{section show=$attribute.content.is_loop}true{/section}" >
        </embed>
        </object>
    {/let}
    </div>
</div>