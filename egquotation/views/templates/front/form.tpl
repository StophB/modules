{extends file=$layout}
{block name='content'}
    <div id="content" class="page-content card card-block">
        <section class="contact-form">
            <form action="" method="post" enctype="multipart/form-data">
                <section class="form-fields">
                    <div class="form-group row">
                        <div class="col-md-9 col-md-offset-3">
                            <h3>Please fill the form</h3>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="email">Email address</label>
                        <div class="col-md-6">
                            <input id="email" class="form-control" name="email" type="email" value="" placeholder="your@email.com" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="id_seller">Are you a whole seller?</label>
                        <div class="col-md-6">
                            <select name="id_seller" id="id_contact" class="form-control form-control-select">
                                <option value="2">Yes</option>
                                <option value="1">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="annual_budget">Your Annual Budget:</label>
                        <div class="col-md-9">
                            <input type="number" id="annual_budget" class="form-control" name="note" spellcheck="false" data-ms-editor="true">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="note">Any special Note:</label>
                        <div class="col-md-9">
                            <textarea id="note" class="form-control" name="note" placeholder="How can we help?" rows="3" spellcheck="false" data-ms-editor="true"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label" for="file-upload">Attachment</label>
                        <div class="col-md-6">
                            <input id="file-upload" type="file" name="fileUpload" class="filestyle">
                            <span class="col-md-3 form-control-comment">optional</span>
                        </div>
                    </div>
                </section>

                <footer class="form-footer text-sm-right">
                    <input class="btn btn-primary" type="submit" name="updateQuote" value="UPDATE">
                    <input class="btn btn-primary" type="submit" name="submitQuote" value="SUBMIT">
                </footer>
            </form>
        </section>

        <section class="quotations-section">
            <h3>Products in Quote</h3>
            {if isset($quotations) && $quotations}
                {foreach from=$quotations item=quotation}
                    <div class="quotation-item">
                    <a href="#">{$quotation.product_name}</a>

                    </div>
                {/foreach}
            {else}
                <p>No quotations available.</p>
            {/if}
        </section>
    </div>

    <!-- Add the following script to initialize Bootstrap FileStyle -->
    <script>
        $(document).ready(function () {
            $(":file").filestyle();
        });
    </script>
{/block}
