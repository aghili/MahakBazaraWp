<?php
/**
 *
 * admin/partials/wp-cbf-admin-display.php - Don't add this comment
 *
 **/
?>
<script>
    var all_records =<?php
        $sync_list = sync_list::get_instance();
        echo $sync_list->count();
    ?>;
</script>
<style type="text/css" rel="stylesheet">
    #update_progress {
        width: 100%;
        height: 30px;
        padding: 5px;
        background-color: white;
    }

    #update_progress_bar {
        width: 1%;
        height: 30px;
        background-color: green;
        text-align: center; /* To center it horizontally (if you want) */
        line-height: 30px; /* To center it vertically */
        color: white;
    }
</style>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <br class="clear"/>

    <div id="col-container">
        <div id="col-right">
            <div class="col-wrap">
                <fieldset>
                    <h2><?php esc_html_e('Sync with bazara', 'wpmbt'); ?></h2>

                    <p><?php esc_html_e('Sync with bazara server for get updates from your account.', 'wpmbt'); ?></p>

                    <div class="misc-pub-section">
                        <button type="button" id="btn_sync" name="btn_sync" class="button">Sync with server</button>
                        <label for="btn_sync">
                            <span><?php esc_attr_e('Last Sync Time ', $this->get_plugin_name()); ?></span><span
                                id="lbl_btn_sync"><?= date("Y-M-d h:i:s", $lastSyncTime) ?></span>
                        </label>
                    </div>
                    <div class="misc-pub-section">

                        <table class="widefat attributes-table wp-list-table ui-sortable" style="width:100%">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e('Sync process', 'wpmbt'); ?></th>
                            </tr>
                            </thead>
                            <tbody id="table-sync-body">
                            <!--                            <tr>-->
                            <!--                                <td >syncing with server <strong>start</strong> .</td>-->
                            <!--                            </tr>-->
                            <!--                            <tr>-->
                            <!--                            <td >syncing <strong>done</strong>,-->
                            <!--                                receive <strong>5420</strong> product and <strong>3242</strong> user.</td>-->
                            <!--                            </tr>-->
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                <fieldset>
                    <h2><?php esc_html_e('Update records', 'wpmbt'); ?></h2>

                    <p><?php esc_html_e('Update received records from bazara server in current shop .', 'wpmbt'); ?></p>

                    <div class="misc-pub-section">
                        <div id="update_progress">
                            <div id="update_progress_bar"></div>
                        </div>
                    </div>
                    <div class="misc-pub-section">
                        <button type="button" id="btn_update_records" name="btn_update_records" class="button">Update
                            sync records
                        </button>
                        <label for="btn_update_records">
                            <span><?php esc_attr_e('last update record time ', $this->get_plugin_name()); ?></span><span
                                id="lbl_btn_update_records"><?= date("Y-M-d h:i:s", time()) ?></span>
                        </label>
                    </div>
                    <div class="misc-pub-section">
                        <table class="widefat attributes-table wp-list-table ui-sortable" style="width:100%">
                            <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e('Update process', 'wpmbt'); ?></th>
                            </tr>
                            </thead>
                            <tbody id="table-update-body">
                            <!--                            <tr>-->
                            <!--                                <td >Updating process <strong>start</strong> .</td>-->
                            <!--                                </tr>-->
                            <!--                            <tr>-->
                            <!--                                <td >update <strong>done</strong>,-->
                            <!--                                    count of <strong>5420</strong> product and <strong>3242</strong> user updated in shop.</td>-->
                            <!--                            </tr>-->
                            </tbody>
                        </table>
                    </div>
                </fieldset>

            </div>
        </div>
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h2><?php esc_html_e('Set settings', 'wpmbt'); ?></h2>

                    <p><?php esc_html_e('Insert your mahak account information ,for communicate with your mahak application.', 'wpmbt'); ?></p>

                    <form method="post" name="bazara_options" action="options.php">

                        <?php
                        //Grab all options
                        //$options = wpmbt_options::GetInstance();
                        //todo: add sync mechanism
                        //todo: add js for edit field for show and hide
                        // Cleanup
                        $option_params = [
                            'bz_user_id' => [
                                'value' => $this->options->options['bz_user_id'],
                                'title' => 'bazara ID:',
                                'desc' => 'Mahak bazara ID',
                                'icon' => '',
                                'edit_tip' => 'Edit mahak ID',
                                'edit_text' => 'Enter mahak ID',
                                'type' => 'email'
                            ],
                            'bz_pass_code' => [
                                'value' => $this->options->options['bz_pass_code'],
                                'title' => 'bazara password:',
                                'desc' => 'Mahak bazara password',
                                'icon' => '',
                                'edit_tip' => 'Edit mahak password',
                                'edit_text' => 'Enter mahak password',
                                'type' => 'password'
                            ],
                            'bz_db_id' => [
                                'value' => $this->options->options['bz_db_id'],
                                'title' => 'Database ID:',
                                'desc' => 'Mahak bazara database ID',
                                'icon' => '',
                                'edit_tip' => 'Edit database ID',
                                'edit_text' => 'Enter database ID',
                                'type' => 'number'
                            ],
                            'bz_mahak_url' => [
                                'value' => $this->options->options['bz_mahak_url'],
                                'title' => 'Mahak bazara website:',
                                'desc' => 'Mahak bazara website URL',
                                'icon' => '',
                                'edit_tip' => 'Edit mahak website URL',
                                'edit_text' => 'Enter URL',
                                'type' => 'url'
                            ],
                            'sync_period_time' => [
                                'value' => $this->options->options['sync_period_time'],
                                'title' => 'Sync period time:',
                                'desc' => 'Sync period time',
                                'icon' => '',
                                'edit_tip' => 'Edit sync period',
                                'edit_text' => 'Enter sync value',
                                'type' => 'number'
                            ],
                            'update_good_per_hint' => [
                                'value' => $this->options->options['update_good_per_hint'],
                                'title' => 'Update per hint:',
                                'desc' => 'Count of update per hint',
                                'icon' => '',
                                'edit_tip' => 'Edit update count',
                                'edit_text' => 'Enter count of update',
                                'type' => 'number'
                            ]
                        ];
                        //$is_login = $options['token']!=null;
                        ?>

                        <?php
                        settings_fields($this->get_plugin_name());
                        do_settings_sections($this->get_plugin_name());
                        ?>

                        <!-- remove some meta and generators from the <head> -->
                        <?php foreach ($option_params as $key => $param) { ?>
                            <div class="misc-pub-section" id="btn_<?= $key ?>">
                                <?php esc_html_e($param['title'], 'wpmbt'); ?>
                                <strong id="btn_<?= $key ?>-display">
                                    <?
                                    if ($param['type'] === 'password') {
                                        if (!empty($param['value']))
                                            echo "is set";
                                        else
                                            echo "not set yet.";
                                    } else
                                        echo $param['value'];
                                    ?>
                                </strong>

                                <a href="#btn_<?= $key ?>"
                                   class="edit-<?= $key ?> btn-collapse hide-if-no-js"><?php esc_html_e('Edit', 'wpmbt'); ?></a>

                                <div id="btn_<?= $key ?>-select" class="hide-if-js">

                                    <input type="hidden" id="btn_<?= $key ?>-value-hid"
                                           name="<?php echo $this->get_plugin_name(); ?>[<?= $key ?>]"
                                           value="<?= $param['value'] ?>"/>
                                    <label for="edit_<?= $key ?>"
                                           class="screen-reader-text"><?= $param['edit_text'] ?></label>
                                    <input type="<?= $param['type'] ?>" id="btn_<?= $key ?>-value"
                                           value="<?= $param['value'] ?>"/>

                                    <p>
                                        <a href="#btn_<?= $key ?>"
                                           class="btn-accept hide-if-no-js button"><?php esc_html_e('OK', 'wpmbt'); ?></a>
                                        <!--                                        <a href="#btn_-->
                                        <? //=$key?><!--" class="cancel-post-visibility hide-if-no-js">-->
                                        <?php //esc_html_e( 'Cancel', 'wpmbt' ); ?><!--</a>-->
                                    </p>
                                </div>
                                <p class="description"><?php esc_html_e($param['desc'], 'wpmbt'); ?></p>
                            </div>
                        <?php } ?>

                        <?php submit_button('Save all changes', 'primary', 'submit', TRUE); ?>

                    </form>
                </div>
            </div>
        </div>
        <?php
        $log = log::get_instance();
        $rows = null;
        if ($log instanceof log)
            $rows = $log->get('sync');
        ?>
    </div>
    <div class="clear"></div>
    <h2><?php esc_html_e('Sync with server log', 'wpmbt'); ?></h2>

    <p><?php esc_html_e('Log of sync with bazara server list .', 'wpmbt'); ?></p>
    <table class="wp-list-table widefat fixed striped stock">
        <thead>
        <tr>
            <th scope="col" id="product" class="manage-column column-primary" width="50">Index</th>
            <th scope="col" id="parent" class="manage-column " width="70">Synced by</th>
            <th scope="col" id="parent" class="manage-column ">Sync start time</th>
            <th scope="col" id="stock_level" class="manage-column " width="100">Product count</th>
            <th scope="col" id="stock_status" class="manage-column " width="100">Customer count</th>
            <th scope="col" id="wc_actions" class="manage-column " width="100">Sync duration</th>
        </tr>
        </thead>

        <tbody id="log-list" data-wp-lists="list:stock">
        <?php
        //                if ($rows){
        //                foreach($rows as $row){
        //                if($row instanceof log_sync_item){
        //                    ?>
        <!--                <tr class="">-->
        <!--                    <td class="" >--><? //=$row->get_id()?><!--</td>-->
        <!--                    <td class="" >--><? //=$row->get_start_time()?><!--</td>-->
        <!--                    <td class="" >--><? //=$row->get_count_product()?><!--</td>-->
        <!--                    <td class="" >--><? //=$row->get_count_customer()?><!--</td>-->
        <!--                    <td class="" >--><? //=$row->get_duration()?><!--</td>-->
        <!--                </tr>-->
        <?php
        //      };
        //                };
        //                } else {
        ?>
        <tr class="no-items">
            <td class="colspanchange" colspan="5">No sync log found.</td>
        </tr>
        <?php
        //                }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="col" id="product" class="manage-column column-primary" width="50">Index</th>
            <th scope="col" id="parent" class="manage-column " width="70">Synced by</th>
            <th scope="col" id="parent" class="manage-column ">Sync start time</th>
            <th scope="col" id="stock_level" class="manage-column " width="100">Product count</th>
            <th scope="col" id="stock_status" class="manage-column " width="100">Customer count</th>
            <th scope="col" id="wc_actions" class="manage-column " width="100">Sync duration</th>
        </tr>
        </tfoot>

    </table>
</div>