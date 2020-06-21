<?php if (count($checks) > 0): ?>
    <div class="col-md-6 col-xs-12 col-sm-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>To Do List</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="">
                    <ul class="to_do">
                        <?php foreach ($checks as $check): ?>
                            <li>
                                <p>
                                    <?= $check ?>
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>