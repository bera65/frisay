</div>
</section>
{include file='./plugin/schema-jsonld.tpl'}
{include file='./_mini/footer1.tpl'}
{include file='./plugin/cart.tpl'}

<script type="text/javascript" src="{$js_dir}jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="{$js_dir}bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="{$js_dir}style.js"></script>
{foreach $moduleAssets.js as $moduleJs}
<script src="{$moduleJs}"></script>
{/foreach}
{if $js}
	<script src="{$js_dir}{$js}"></script>
{/if}
{include file='./_mini/priceAllert.tpl'}
<div id="tostAlert" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="d-flex">
	<div class="toast-body">
	  
	</div>
	<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
</div>
</body>
</html>
