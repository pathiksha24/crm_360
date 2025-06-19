<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();  ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mtop5">
               <div id="frame">
                  <div id="sidepanel">
                     <div id="profile">
                        <div class="wrap">
                           <img id="profile-img" src="<?php echo staff_profile_image_url($staff->staffid); ?>" class="online" alt="" />
                           <p><?php echo $staff->full_name; ?></p>
                           <!-- <i class="fa fa-chevron-down expand-button" aria-hidden="true"></i> -->
                           <div id="status-options">
                              <ul>
                                 <li id="status-online" class="active"><span class="status-circle"></span>
                                    <p>Online</p>
                                 </li>
                                 <li id="status-away"><span class="status-circle"></span>
                                    <p>Away</p>
                                 </li>
                                 <li id="status-busy"><span class="status-circle"></span>
                                    <p>Busy</p>
                                 </li>
                                 <li id="status-offline"><span class="status-circle"></span>
                                    <p>Offline</p>
                                 </li>
                              </ul>
                           </div>
                           <!-- <div id="expanded">
                 <label for="phone"><i class="fa fa-phone fa-fw" aria-hidden="true"></i></label>
                 <label for="facebook"><i class="fa fa-facebook fa-fw" aria-hidden="true"></i></label>
                 <label for="linkedin"><i class="fa fa-linkedin fa-fw" aria-hidden="true"></i></label>
              </div> -->
                        </div>
                     </div>
                     <div id="search">
                        <label for=""><i class="fa fa-search" aria-hidden="true"></i></label>
                        <input id="serch-input" type="text" ctype="lead" placeholder="<?php echo _l('lead_manager_conversation_serch_cont'); ?>" onkeyup="serachContacts(this);" />
                     </div>
                     <div id="contacts">
                        <ul id="lead-contacts" class="tabcontent">
                           <?php
                           $first_lead = '';
                           if (isset($leads) && !empty($leads)) {
                              foreach ($leads as $lead) {
                                 if (!is_numeric($first_lead)) {
                                    $first_lead = $lead['id'];
                                 }
                                 $last_conversation = get_last_message_conversation($lead['id'], ['is_client' => 'no']);
                           ?>
                                 <li class="contact" onclick="loadContent(<?php echo $lead['id']; ?>);" id="<?php echo $lead['id'] . '_contact'; ?>">
                                    <div class="wrap">
                                       <img src="<?php echo base_url('assets/images/user-placeholder.jpg'); ?>" alt="" />
                                       <div class="meta">
                                          <p class="name"><?php echo $lead['name']; ?></p>
                                          <small><?php echo isset($lead['phonenumber']) && !empty($lead['phonenumber']) ? $lead['phonenumber'] : _l('NA'); ?></small>
                                          <p class="preview"><?php echo isset($last_conversation->sms_body) && !empty($last_conversation->sms_body) ? $last_conversation->sms_body : ''; ?></p>
                                          <div class="count_unread_div"></div>
                                       </div>
                                    </div>
                                 </li>
                           <?php }
                           } ?>
                        </ul>
                        <ul id="client-contacts" class="tabcontent hidden">
                           <?php
                           if (isset($clients) && !empty($clients)) {
                              foreach ($clients as $client) {
                                 $primary_contact_id = get_primary_contact_user_id($client['userid']);
                                 if (isset($primary_contact_id) && !empty($primary_contact_id)) {
                                    $profile_image = contact_profile_image_url($primary_contact_id);
                                    $last_conversation = get_last_message_conversation($client['userid'], ['is_client' => 'yes']);
                           ?>
                                    <li class="contact" onclick="loadContent(<?php echo $client['userid']; ?>);" id="<?php echo $client['userid'] . '_contact'; ?>">
                                       <div class="wrap">
                                          <img src="<?php echo $profile_image; ?>" alt="contact" />
                                          <div class="meta">
                                             <p class="name"><?php echo $client['company']; ?></p>
                                             <small><?php echo isset($client['phonenumber']) && !empty($client['phonenumber']) ? $client['phonenumber'] : _l('NA'); ?></small>
                                             <p class="preview"><?php echo isset($last_conversation->sms_body) && !empty($last_conversation->sms_body) ? $last_conversation->sms_body : ''; ?></p>
                                             <div class="count_unread_div"></div>
                                          </div>
                                       </div>
                                    </li>
                           <?php }
                              }
                           } ?>
                        </ul>
                     </div>
                     <div id="bottom-bar">
                        <button id="btn-leads" class="active_btn" onclick="openContactTab('lead-contacts');"><i class="fa fa-users fa-fw" aria-hidden="true"></i> <span><?php echo _l('lead_manager_lead'); ?></span></button>
                        <button id="btn-clients" onclick="openContactTab('client-contacts');"><i class="fa fa-users fa-fw" aria-hidden="true"></i> <span><?php echo _l('lead_manager_client'); ?></span></button>
                     </div>
                  </div>
                  <div class="content" id="conversation">

                  </div>
               </div>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   selectedLeadId = "<?php echo $first_lead; ?>";
   $(function() {
      loadContent(selectedLeadId);
      setInterval(incoming_unread_sms, 8000);
   });
   $(document).on('click', 'button.submit', function(event) {
      selectedLeadId = $(event.target).data('lead');
      var type = $(event.target).data('type');
      if (selectedLeadId && type) {
         newMessageOutgoing(selectedLeadId, type, $(event.target));
      } else {
         alert("something went wrong plz refresh the page!");
         return false;
      }
   });
   $(window).on('keypress', function(event) {
      if (event.which == 13) {
         var _button = $(event.target).closest('div.wrap').find('button');
         selectedLeadId = _button.data('lead');
         var type = _button.data('type');
         if (selectedLeadId && type) {
            newMessageOutgoing(selectedLeadId, type, _button);
         } else {
            alert("something went wrong plz refresh the page!");
            return false;
         }
      }
   });
</script>
</body>

</html>