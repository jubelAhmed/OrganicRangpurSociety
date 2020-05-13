<?php
if ( ! defined( 'ABSPATH' ) ) {
     exit;
 }                                            
 ?>
 <style type="text/css">
  .crm_fields_table{
  width: 100%; margin-top: 30px;
  }.crm_fields_table .crm_field_cell1 label{
  font-weight: bold; font-size: 14px;
  }
  .crm_fields_table .clear{
  clear: both;
  }
  .crm_fields_table .crm_field{
  margin: 20px 0px;   
  }
  .crm_fields_table .crm_text{
  width: 100%;
  }
  .crm_fields_table .crm_field_cell1{
  width: 20%; min-width: 100px; float: left; display: inline-block;
  line-height: 26px;
  }
  .crm_fields_table .crm_field_cell2{
  width: 80%; float: left; display: inline-block;
  }
  .vxc_alert{
  padding: 10px 20px;
  }
  .vx_icons{
      color: #888;
  }
  .vx_green{
    color:rgb(0, 132, 0);  
  }
  #tiptip_content{
      max-width: 200px;
  }
  .vx_tr{
      display: table; width: 100%;
  }
  .vx_td{
      display: table-cell; width: 90%;
  }
  .vx_td2{
      display: table-cell; 
  }
 .crm_field .vx_td2 .vx_toggle_btn{
      margin: 0 0 0 10px; vertical-align: baseline; width: 80px;
  }

    .submit_vx{
 padding-top: 10px;
  margin-top: 20px; 
  }
    .crm_fields_table input , .crm_fields_table select{
      margin: 0px;
  }
      .vx_accounts_table .vx_pointer{
      cursor: pointer;
  }
  .vx_accounts_table .fa-caret-up , .vx_accounts_table .fa-caret-down{
      display: none;
  }
  .vx_accounts_table th.headerSortUp .fa-caret-down{ 
display: inline; 
} 
  .vx_accounts_table th.headerSortDown .fa-caret-up{ 
display: inline; 
}

  </style> 
    <script type="text/javascript">
  jQuery(document).ready(function($){

    $(document).on('click','.vx_toggle_key',function(e){
  e.preventDefault();  
  var key=$(this).parents(".vx_tr").find(".crm_text"); 
  if($(this).hasClass('vx_hidden')){
  $(this).text('<?php _e('Show Key','gravity-forms-googlesheets-crm') ?>');  
  $(this).removeClass('vx_hidden');
  key.attr('type','password');  
  }else{
  $(this).text('<?php _e('Hide Key','gravity-forms-googlesheets-crm') ?>');  
  $(this).addClass('vx_hidden');
  key.attr('type','text');  
  }
  });
  });
  </script> 
 <div class="vx_wrap">
  <h2  style="margin-bottom: 12px; line-height: 36px">  <img alt="<?php _e("Gravity Forms Google Sheets Plugin Settings", 'gravity-forms-googlesheets-crm') ?>" title="<?php _e("Gravity Forms Google Sheets Plugin Settings", 'gravity-forms-googlesheets-crm') ?>" src="<?php echo $this->get_base_url()?>images/googlesheets-crm-logo.png?ver=1" style="float:left; margin:0 7px 10px 0;" height="46" /> <?php _e("Gravity Forms Google Sheets Plugin Settings", 'gravity-forms-googlesheets-crm'); ?>  </h2>
  <div class="clear"></div>
  <?php 

   if(is_array($msgs) && count($msgs)>0){      
    foreach($msgs as $msg){
     if(isset($msg['class']) && $msg['class'] !=""){
          ?>
  <div class="fade below-h2 <?php echo $msg['class'] ?> notice is-dismissible">
  <p><?php echo  $msg['msg']?></p>
  </div>
  <?php
      }     }
   }                
    ?>
  <form method="post" id="mainform">
  <?php wp_nonce_field("vx_nonce") ?>
  <h2><?php 
  if(empty($id)){
  _e("Google Sheets Account Information", 'gravity-forms-googlesheets-crm');
  }else{
  _e("Google Sheets Account #", 'gravity-forms-googlesheets-crm'); echo $id;    
  }
  if(empty($id) || $new_account_id != $id){
 ?> <a href="<?php echo $new_account ?>" class="add-new-h2" title="<?php _e('Add New Account','gravity-forms-googlesheets-crm'); ?>"><?php _e('Add New Account','gravity-forms-googlesheets-crm'); ?></a> 
 <?php
}
if(!empty($id)){
 ?>
 <a href="<?php echo $page_link ?>" class="add-new-h2" title="<?php _e('Back to Accounts','gravity-forms-googlesheets-crm'); ?>"> <?php _e('Back to Accounts','gravity-forms-googlesheets-crm'); ?></a>
 <?php
}
 ?>
  </h2>
  <p style="text-align: left;"> <?php echo sprintf(__("If you don't have a Google account, you can %ssign up for one here%s.", 'gravity-forms-googlesheets-crm'), "<a href='https://accounts.google.com' target='_blank' title='".__('Sign Up for Google Sheets CRM','gravity-forms-googlesheets-crm')."'>" , "</a>") ?> </p>
<?php 

    if(!empty($id)){
          $name=$this->post('name',$info); 
     include_once(self::$path . "templates/setting.php");   
    }else{
    include_once(self::$path . "templates/settings-table.php");        
    }
     do_action('vx_plugin_upgrade_notice_'.$this->type);
    ?>
 <div>
  <div class="hr-divider" style="margin: -10px 0 24px 0"></div>
<h3><?php _e('Optional Settings','gravity-forms-googlesheets-crm');  ?></h3>

<table class="form-table">
  <tr>
  <th scope="row"><label for="vx_plugin_data"><?php _e("Plugin Data", 'gravity-forms-googlesheets-crm'); ?></label>
  </th>
  <td>
<label for="vx_plugin_data"><input type="checkbox" name="meta[plugin_data]" value="yes" <?php if($this->post('plugin_data',$meta) == "yes"){echo 'checked="checked"';} ?> id="vx_plugin_data"><?php _e('On deleting this plugin remove all of its data','gravity-forms-googlesheets-crm'); ?></label>
  </td>
  </tr>     


</table>
 <p class="submit">
   <button type="submit" value="save" class="button-primary" title="<?php _e('Save Changes','gravity-forms-googlesheets-crm'); ?>" name="save"><?php _e('Save Changes','gravity-forms-googlesheets-crm'); ?></button>
  <input type="hidden" name="vx_meta" value="1"> 
 </p>
</div>
  </form>

  <?php
  do_action('add_section_'.$this->id);
    if(current_user_can($this->id."_uninstall")){ 
  ?>
  <form action="" method="post">
  <?php wp_nonce_field("vx_nonce") ?>
  <?php if(current_user_can($this->id."_uninstall")){ ?>
  <div class="hr-divider"  style="margin: -10px 0 24px 0"></div>
  <h3><?php _e("Uninstall Google Sheets Plugin", 'gravity-forms-googlesheets-crm') ?></h3>
  <div class="delete-alert alert_red">
  <h3>
  <?php _e('Warning', 'gravity-forms-googlesheets-crm'); ?>
  </h3>
  <p><?php _e("This operation deletes ALL Google Sheets Feeds. ", 'gravity-forms-googlesheets-crm') ?></p>
  <?php
  $uninstall_button = '<input type="submit" name="'.$this->id.'_uninstall" title="'.__('Uninstall','gravity-forms-googlesheets-crm').'" value="' . __("Uninstall Google Sheets Plugin", 'gravity-forms-googlesheets-crm') . '" class="button" onclick="return confirm(\'' .__('Warning! ALL Google Sheets Feeds and Logs will be deleted. This cannot be undone. (OK) to delete, (Cancel) to stop', 'gravity-forms-googlesheets-crm') . '\');"/>';
  echo  $uninstall_button;
  ?>
  </div>
  <?php } ?>
  </form>
  <?php
  }
  ?>
  </div>
  <script type="text/javascript">
  jQuery(document).ready(function($){

             var unsaved=false;
      $('#mainform :input').change(function(){
      if(!$(this).attr('data-save')){ 
        unsaved=true;
      }
      });
       $('#mainform').submit(function(){ 
        unsaved=false;
      });
      
      $(window).bind("beforeunload",function(event) { 
    if(unsaved) return 'Changes you made may not be saved';
});
      
      $("#vx_refresh_lists").click(function(){
       $(this).val("true");   
      });


  $(document).on('click','#vx_revoke',function(e){
  
  if(!confirm('<?php _e('Notification - Remove Connection?','gravity-forms-googlesheets-crm'); ?>')){
  e.preventDefault();   
  }
  })  
  })
  </script>