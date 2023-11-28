{if $active}
    <div class="tabs">
        <div class="container" id="custom-text">
            <h1 class="mb-3">Custom Text</h1>
            <h3 class="text-center">
                {l s=$title mod='mysimplemodule'}
            </h3>
            <p>
                {l s=$text mod='mysimplemodule'}
            </p>
        </div>
    </div>
{/if}