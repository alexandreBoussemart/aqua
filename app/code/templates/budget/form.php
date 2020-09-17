<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Enregistrer une dépense</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit_budget" value="1"/>
                <div class="item form-group">
                    <label class="col-form-label col-md-3 col-sm-3 label-align">Date Of Birth <span class="required">*</span>
                    </label>
                    <div class="col-md-9 col-sm-9">
                        <input id="date"
                               name="date"
                               class="date-picker form-control"
                               placeholder="jj-mm-aaaa"
                               required="required"
                               type="text"
                               onfocus="this.type='date'"
                               onmouseover="this.type='date'"
                               onclick="this.type='date'"
                               onblur="this.type='text'"
                               onmouseout="timeFunctionLong(this)">
                        <script>
                            function timeFunctionLong(input) {
                                setTimeout(function() {
                                    input.type = 'text';
                                }, 60000);
                            }
                        </script>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Commentaire<span class="required">*</span>
                    </label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="comment" class="date-picker form-control col-md-7 col-xs-12" required="required"
                               type="text" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Prix<span class="required">*</span>
                    </label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="value" class="date-picker form-control col-md-7 col-xs-12" required="required"
                               type="text" />
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>