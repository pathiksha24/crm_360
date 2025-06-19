<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="contact-profile">
   <?php if($is_client){
      $primary_contact_id = get_primary_contact_user_id($lead->userid);
      if(isset($primary_contact_id) && !empty($primary_contact_id)){
         $profile_image = contact_profile_image_url($primary_contact_id);
      }
      echo '<img src="'.$profile_image.'" alt="" />';
      echo '<p>'.$lead->company.'<small>'.$lead->phonenumber.'</small></p>';
   }else{ 
      echo '<img src="'.base_url("assets/images/user-placeholder.jpg").'" alt="" />';
      echo '<p>'.$lead->name.'<small>'.$lead->phonenumber.'</small></p>';
   } ?>
</div>
<div class="messages">
   <ul id="messages-ul">
      <?php 
      if(isset($chats) && !empty($chats)){ 
         foreach($chats as $chat){
            ?>
            <li id="<?php echo $chat['id']; ?>" class="<?php echo $chat['sms_direction']; ?>">
               <?php if($chat['sms_direction'] == 'incoming'){
                  echo '<img src="'.base_url('assets/images/user-placeholder.jpg').'" alt="" />';
               }else{
                if(isset($staff->profile_image) && !empty($staff->profile_image)){
                 echo '<img src="'.$staff->profile_image.'" alt="" />';
              }else{
                 echo '<img src="'.base_url('assets/images/user-placeholder.jpg').'" alt="" />';
              }
           }?>
           <p><?php echo $chat['sms_body']; ?></p>
           <small><?php echo _dt($chat['added_at']); ?></small>
           <span class="sms_status"><?php echo $chat['sms_status']; ?></span>
        </li>
     <?php }} ?>
  </ul>
</div>
<div class="message-input">
   <div class="wrap">
      <input type="text" placeholder="Write your message..." />
      <!-- <i class="fa fa-paperclip attachment" aria-hidden="true"></i> -->
      <?php if($is_client){
         echo '<button class="submit" data-lead="'.$lead->userid.'" data-type="client"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>';
      }else{
         echo '<button class="submit" data-lead="'.$lead->id.'" data-type="lead"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>';
      }?>
   </div>
</div>