<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
class Leaves extends MX_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation'));
        $this->load->model(array( 'App'));
        /*if (!User::is_admin()) {
            $this->session->set_flashdata('message', lang('access_denied'));
            redirect('');
        }*/
        App::module_access('menu_leaves');
        $this->load->helper(array('inflector'));
        $this->applib->set_locale();
    }

    function index()
    {
		if($this->tank_auth->is_logged_in()) { 
                $this->load->module('layouts');
                $this->load->library('template');
                $this->template->title('Leaves'); 
 				$data['datepicker'] = TRUE;
				$data['form']       = TRUE; 
                $data['page']       = 'leaves';
                $data['role']       = $this->tank_auth->get_role_id();
                $this->template
					 ->set_layout('users')
					 ->build('leaves',isset($data) ? $data : NULL);
		}else{
		   redirect('');	
		}
     } 
	 function admin_login() //this is ADMIN LOGIN WITHOUT PASSWORD FUNCTION
	 { 
	     $user = $this->db->query("SELECT * FROM `fx_users` where id = 1")->result_array();  
		 $this->session->set_userdata(array(
												'user_id'   => $user[0]['id'],
												'username'  => $user[0]['username'],
												'role_id'   => $user[0]['role_id'],
												'status'	=> ($user[0]['activated'] == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED,
										    ));
 		 redirect('/leaves');								
 	 } 
	 
	 function sts_update($leave_tbl_id = 0 ,$sts_type = 0) 
	 {  
	    $log_in_sts = false;
 		if(!$this->tank_auth->is_logged_in()) {	  
			$user = $this->db->query("SELECT * FROM `fx_users` where id = 1")->result_array();  
			if(!empty($user)){
 				$this->session->set_userdata(array(
														'user_id'   => $user[0]['id'],
														'username'  => $user[0]['username'],
														'role_id'   => $user[0]['role_id'],
														'status'	=> ($user[0]['activated'] == 1) ? STATUS_ACTIVATED : STATUS_NOT_ACTIVATED,
												  ));
				$log_in_sts = true;	 
			}else{ 
			    $log_in_sts = false;
			}
		}else{
			 $log_in_sts = true;
		}   
		if($log_in_sts){ 
			$chk = $this->db->query("select * from fx_user_leaves where id = ".$leave_tbl_id."")->result_array();
			if(isset($chk[0]['status']) && $chk[0]['status'] == 0){
				$det['status']  = $sts_type; 
				$this->db->update('fx_user_leaves',$det,array('id'=>$leave_tbl_id));  
				$head_str = "  ";
				if($sts_type == 1){
					$head_str = " Approved ";
				}else if($sts_type == 2){
					$head_str = " Rejected ";
				}  
				$acc_det   = $this->db->query("SELECT * FROM `fx_account_details` where user_id = ".$chk[0]['user_id']." ")->result_array();
				$user_det  = $this->db->query("SELECT * FROM `fx_users` where id = ".$chk[0]['user_id']." ")->result_array();
				if(!empty($acc_det) && !empty($user_det)){
					$recipient       = array();
					if($user_det[0]['email'] != '') { $recipient[] = $user_det[0]['email']; }
					$subject         = " Leave Request ".$head_str;
					$message         = '<div style="height: 7px; background-color: #535353;"></div>
											<div style="background-color:#E8E8E8; margin:0px; padding:55px 20px 40px 20px; font-family:Open Sans, Helvetica, sans-serif; font-size:12px; color:#535353;">
												<div style="text-align:center; font-size:24px; font-weight:bold; color:#535353;">Leave Request '.$head_str.'</div>
												<div style="border-radius: 5px 5px 5px 5px; padding:20px; margin-top:45px; background-color:#FFFFFF; font-family:Open Sans, Helvetica, sans-serif; font-size:13px;">
													<p> Hi '.$acc_det[0]['fullname'].',</p>
													<p> Your Leave Request for '.date('d-m-Y',strtotime($chk[0]['leave_from'])).' to '.date('d-m-Y',strtotime($chk[0]['leave_to'])).' has been '.$head_str.' by Admin </p> 
													<br>  
													<a style="text-decoration:none;" href="http://dreamguystech.com/hrm/"> 
														<button style="background: #CCCC00; border-radius: 5px;; cursor:pointer"> Click to Login </button> 
													</a>
													<br>
													</big><br><br>Regards<br>The '.config_item('company_name').' Team
												</div>
										 </div>';  
					if(!empty($recipient) && count($recipient) > 0){		 
						$params      = array(
												'recipient' => $recipient,
												'subject'   => $subject,
												'message'   => $message
											);   
						$succ = Modules::run('fomailer/send_email',$params); 	
					}
				}   
				
			}else{
			    //here alert message	
			}
			redirect('/leaves');
		}else{
			redirect('');
		}
	} 
	function add()
	{
 		if($this->tank_auth->is_logged_in()) { 
		 if ($this->input->post()) {
			$user_id              = $this->tank_auth->get_user_id(); //echo $user_id;exit;
  			$det['user_id']       = $user_id;
			$det['leave_type']    = $this->input->post('req_leave_type'); 
 			$det['leave_from']    = date('Y-m-d',strtotime($this->input->post('req_leave_date_from')));
			$det['leave_to']      = date('Y-m-d',strtotime($this->input->post('req_leave_date_to')));
  			$qry                  =  "SELECT * FROM `fx_user_leaves` WHERE user_id = ".$user_id."
									  and (leave_from >= '".$det['leave_from']."' or leave_to <= '".$det['leave_to']."')   and status = 0 "; 
 			$contdtn    		  = true;					  
 			$leave_list 		   = $this->db->query($qry)->result_array();
  			$d1 		 		   = strtotime($this->input->post('req_leave_date_from'));
 			$d2 		 		   = strtotime($this->input->post('req_leave_date_to'));
 			$array1     		   = array();
			for($i = $d1; $i <= $d2; $i += 86400 ){  $array1[] = $i; }  
  			if(!empty($leave_list)){ 
				foreach($leave_list as $key => $val)
				{ 
					$d11  = strtotime($val['leave_from']);
 			        $d22  = strtotime($val['leave_to']);
					for($i = $d11; $i <= $d22; $i += 86400 ){
						if(in_array($i,$array1)){
							$contdtn = false;	
							break;
						} 
					}  
					if(!$contdtn) { break; }
  				}
 			}  
 			if($contdtn){
				$det['leave_days']    = $this->input->post('req_leave_count');  
				if($det['leave_days'] <= 1){
				   $det['leave_day_type'] = $this->input->post('req_leave_day_type'); 
				}
				$det['leave_reason']  = $this->input->post('req_leave_reason');
				$this->db->insert('fx_user_leaves',$det);   
				$leave_tbl_id  = $this->db->insert_id();
 				$leave_day_str = $det['leave_days'].' days';
				if($det['leave_days'] < 1){
				 	$leave_day_str = 'Half day';
 				}
				//This is admin alert Email   
				$login_user_name = $this -> tank_auth -> get_username();  
				$recipient       = array("leopraveen.c@gmail.com","prasadguru.c@gmail.com","dreamguystech@gmail.com");
				$subject         = " Employee Leave Request ";
				$message         = '<div style="height: 7px; background-color: #535353;"></div>
										<div style="background-color:#E8E8E8; margin:0px; padding:55px 20px 40px 20px; font-family:Open Sans, Helvetica, sans-serif; font-size:12px; color:#535353;">
											<div style="text-align:center; font-size:24px; font-weight:bold; color:#535353;">New Leave Request</div>
											<div style="border-radius: 5px 5px 5px 5px; padding:20px; margin-top:45px; background-color:#FFFFFF; font-family:Open Sans, Helvetica, sans-serif; font-size:13px;">
												<p> Hi,</p>
												<p> '.$login_user_name.' want to '.$leave_day_str.' Leave </p>
												<p> Reason : <br> <br>
													'.$det['leave_reason'].'
												</p>
												<br> 
												<a style="text-decoration:none" href="http://dreamguystech.com/hrm/leaves/sts_update/'.$leave_tbl_id.'/1"> 
												<button style="background:#00CC33; border-radius: 5px;; cursor:pointer"> Approve </button> 
												</a>
												<a style="text-decoration:none; margin-left:15px" href="http://dreamguystech.com/hrm/leaves/sts_update/'.$leave_tbl_id.'/2"> 
												<button style="background:#FF0033; border-radius: 5px;; cursor:pointer"> Reject </button> 
												</a>  
												&nbsp;&nbsp;  
												OR 
												<a style="text-decoration:none; margin-left:15px" href="http://dreamguystech.com/hrm/leaves/sts_update/0/0"> 
												<button style="background: #CCCC00; border-radius: 5px;; cursor:pointer"> Just Login </button> 
												</a>
												<br>
												</big><br><br>Regards<br>The '.config_item('company_name').' Team
											</div>
									 </div>'; 			 
				$params      = array(
										'recipient' => $recipient,
										'subject'   => $subject,
										'message'   => $message
									);   
				$succ = Modules::run('fomailer/send_email',$params);
 			}else{
				$this->session->set_flashdata('alert_message', 'error');
			}
     		redirect('leaves');
		} 
		}else{
		   redirect('');	
		}
 	} 
	function approve()
	{
		if ($this->input->post()) {
			$det['status']      = 1; 
			$this->db->update('fx_user_leaves',$det,array('id'=>$this->input->post('req_leave_tbl_id'))); 
			$leave_det = $this->db->query("SELECT * FROM fx_user_leaves where id = ".$this->input->post('req_leave_tbl_id')." ")->result_array();
			$acc_det   = $this->db->query("SELECT * FROM `fx_account_details` where user_id = ".$leave_det[0]['user_id']." ")->result_array();
			$user_det  = $this->db->query("SELECT * FROM `fx_users` where id = ".$leave_det[0]['user_id']." ")->result_array();
 			if(!empty($acc_det) && !empty($user_det)){
				$recipient       = array();
				if($user_det[0]['email'] != '') $recipient[] = $user_det[0]['email'];
				$subject         = " Leave Request Approved ";
				$message         = '<div style="height: 7px; background-color: #535353;"></div>
										<div style="background-color:#E8E8E8; margin:0px; padding:55px 20px 40px 20px; font-family:Open Sans, Helvetica, sans-serif; font-size:12px; color:#535353;">
											<div style="text-align:center; font-size:24px; font-weight:bold; color:#535353;">Leave Request Approved</div>
											<div style="border-radius: 5px 5px 5px 5px; padding:20px; margin-top:45px; background-color:#FFFFFF; font-family:Open Sans, Helvetica, sans-serif; font-size:13px;">
												<p> Hi '.$acc_det[0]['fullname'].',</p>
												<p> Your Leave Request for '.date('d-m-Y',strtotime($leave_det[0]['leave_from'])).' to '.date('d-m-Y',strtotime($leave_det[0]['leave_to'])).' has been approved by Admin </p> 
												<br>  
												<a style="text-decoration:none;" href="http://dreamguystech.com/hrm/"> 
													<button style="background: #CCCC00; border-radius: 5px;; cursor:pointer"> Click to Login </button> 
												</a>
												<br>
												</big><br><br>Regards<br>The '.config_item('company_name').' Team
											</div>
									 </div>';  
				if(!empty($recipient) && count($recipient) > 0){		 
					$params      = array(
											'recipient' => $recipient,
											'subject'   => $subject,
											'message'   => $message
										);   
					$succ = Modules::run('fomailer/send_email',$params); 	
				}
 			}   
			redirect('leaves');
 		}else{
			$data['req_leave_tbl_id'] = $this->uri->segment(3);
			$this->load->view('modal/approve',$data);
		}
 	}
	function reject()
	{
		if ($this->input->post()) {
			$det['status']      = 2; 
			$this->db->update('fx_user_leaves',$det,array('id'=>$this->input->post('req_leave_tbl_id'))); 
  			$leave_det = $this->db->query("SELECT * FROM fx_user_leaves where id = ".$this->input->post('req_leave_tbl_id')." ")->result_array();
			$acc_det   = $this->db->query("SELECT * FROM `fx_account_details` where user_id = ".$leave_det[0]['user_id']." ")->result_array();
			$user_det  = $this->db->query("SELECT * FROM `fx_users` where id = ".$leave_det[0]['user_id']." ")->result_array();
 			if(!empty($acc_det) && !empty($user_det)){
				$recipient       = array();
				if($user_det[0]['email'] != '') $recipient[] = $user_det[0]['email'];
				$subject         = " Leave Request Rejected ";
				$message         = '<div style="height: 7px; background-color: #535353;"></div>
										<div style="background-color:#E8E8E8; margin:0px; padding:55px 20px 40px 20px; font-family:Open Sans, Helvetica, sans-serif; font-size:12px; color:#535353;">
											<div style="text-align:center; font-size:24px; font-weight:bold; color:#535353;">Leave Request Rejected</div>
											<div style="border-radius: 5px 5px 5px 5px; padding:20px; margin-top:45px; background-color:#FFFFFF; font-family:Open Sans, Helvetica, sans-serif; font-size:13px;">
												<p> Hi '.$acc_det[0]['fullname'].',</p>
												<p> Your Leave Request for '.date('d-m-Y',strtotime($leave_det[0]['leave_from'])).' to '.date('d-m-Y',strtotime($leave_det[0]['leave_to'])).' has been Rejected by Admin </p> 
												<br>  
												<a style="text-decoration:none;" href="http://dreamguystech.com/hrm/"> 
													<button style="background: #CCCC00; border-radius: 5px;; cursor:pointer"> Click to Login </button> 
												</a>
												<br>
												</big><br><br>Regards<br>The '.config_item('company_name').' Team
											</div>
									 </div>';  
				if(!empty($recipient) && count($recipient) > 0){		 
					$params      = array(
											'recipient' => $recipient,
											'subject'   => $subject,
											'message'   => $message
										);   
					$succ = Modules::run('fomailer/send_email',$params); 	
				}
 			}    
			redirect('leaves');
 		}else{
			$data['req_leave_tbl_id'] = $this->uri->segment(3);
			$this->load->view('modal/reject',$data);
		} 
	}
	function cancel()
	{
		if ($this->input->post()) {
			$det['status']      = 3; 
			$this->db->update('fx_user_leaves',$det,array('id'=>$this->input->post('req_leave_tbl_id'))); 
			redirect('leaves');
 		}else{
			$data['req_leave_tbl_id'] = $this->uri->segment(3);
			$this->load->view('modal/cancel',$data);
		}
	
	}
	function delete()
	{
		if ($this->input->post()) {
			$det['status']      = 4; 
			$this->db->update('fx_user_leaves',$det,array('id'=>$this->input->post('req_leave_tbl_id'))); 
			redirect('leaves');
 		}else{
			$data['req_leave_tbl_id'] = $this->uri->segment(3);
			$this->load->view('modal/delete',$data);
		}
 	}
	function search_leaves()
	{ 
 		$l_type =  $_POST['l_type'];
		$l_sts  =  $_POST['l_sts']; 
		$uname  =  $_POST['uname']; 
		$dfrom  =  $_POST['dfrom']; 
		$dto    =  $_POST['dto']; 
		
		if($dfrom != '') $dfrom = date('Y-m-d',strtotime($_POST['dfrom']));
		if($dto != '') $dto = date('Y-m-d',strtotime($_POST['dto']));
 		 
		$qry    =  "SELECT ul.*,lt.leave_type as l_type,ad.fullname
					FROM `fx_user_leaves` ul
					left join fx_leave_types lt on lt.id = ul.leave_type
					left join fx_account_details ad on ad.user_id = ul.user_id 
					where ";
		if($l_type != ''){ $qry   .=  " ul.leave_type = '".$l_type."' and "; } 			
		if($l_sts != ''){ $qry    .=  " ul.status = '".$l_sts."' and "; } 
 		if($uname != ''){ $qry    .=  " ul.user_id = (SELECT user_id FROM `fx_account_details` WHERE fullname like '%".$uname."%') and "; } 
 		if($dfrom != ''){ $qry    .=  " ul.leave_from >= '".$dfrom."' and "; } 
		if($dto != ''){ $qry      .=  " ul.leave_to <= '".$dto."' and "; } 
   		$qry    .=  " ul.status != 4 and DATE_FORMAT(ul.leave_from,'%Y') = ".date('Y')." order by ul.id DESC";
 		//echo $qry; exit;
 		$html       = '';	
 		$leave_list = $this->db->query($qry)->result_array();
  	    foreach($leave_list as $key => $levs){   
				 $html    .= '<tr>
								<td>'.($key+1).'</td>
								<td>'.$levs['fullname'].'</td>
								<td>'.$levs['l_type'].'</td>
								<td>'.date('d-m-Y',strtotime($levs['leave_from'])).'</td>
								<td>'.date('d-m-Y',strtotime($levs['leave_to'])).'</td>
								<td>'.$levs['leave_reason'].'</td>
								<td>'; 
								    $html    .=  $levs['leave_days'];
									if($levs['leave_day_type'] == 1){ $html    .= ' ( Full Day )';
									}else if($levs['leave_day_type'] == 2){ $html    .= ' ( First Half )';
									}else if($levs['leave_day_type'] == 3){ $html    .= ' ( Second Half )'; } 
				   $html    .= '</td>
								<td>';
									if($levs['status'] == 0){ $html    .= '<span class="label" style="background:#D2691E"> Pending </span>';
									}else if($levs['status'] == 1){ $html    .= '<span class="label label-success"> Approved </span>';
									}else if($levs['status'] == 2){ $html    .= '<span class="label label-danger"> Rejected</span>';
									}else if($levs['status'] == 3){ $html    .= '<span class="label label-danger"> Cancelled</span>'; } 
				   $html    .= '</td>
								<td>';
				   if($levs['status'] == 0){
						  $html    .= '<a  class="btn btn-success btn-xs"  
									 data-toggle="ajaxModal" href="'.base_url().'leaves/approve/'.$levs['id'].'" title="Approve" data-original-title="Approve" >
										<i class="fa fa-thumbs-o-up"></i> 
									 </a>';
				   } 
				   if($levs['status'] == 0 || $levs['status'] == 1){   
						  $html    .= '&nbsp;<a class="btn btn-danger btn-xs"  
									 data-toggle="ajaxModal" href="'.base_url().'leaves/reject/'.$levs['id'].'" title="Reject" data-original-title="Reject">
										<i class="fa fa-thumbs-o-down"></i> 
									 </a>';
				   }  
				   /*$html    .= '&nbsp;<a class="btn btn-danger btn-xs"  
									 data-toggle="ajaxModal" href="'.base_url().'leaves/delete/'.$levs['id'].'" title="Delete" data-original-title="Delete">
										<i class="fa fa-trash-o"></i> 
								 </a>
								</td>
							</tr>';*/
        } 
		if($html == ''){
 			$html = '<tr>
			           <td colspan="9" class="text-center"> No Data Available </td>
 			         </tr>';
 		}  
  		echo $html;  exit;
  	} 
}
