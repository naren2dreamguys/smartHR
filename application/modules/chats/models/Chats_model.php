<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
class Chats_model extends CI_Model
{
  function user_chat_history($user_id)
	{
		$usr_qry = "SELECT c.chat_id,
					 CASE
						WHEN c.chat_from = ".$user_id." THEN (chat_to)
						WHEN c.chat_from != ".$user_id." THEN (chat_from)
						ELSE 0
					 END AS user_id,
					 CASE
						WHEN c.chat_from = ".$user_id." THEN (SELECT  fullname FROM fx_account_details WHERE user_id = c.chat_to)
						WHEN c.chat_from != ".$user_id." THEN (SELECT fullname FROM fx_account_details WHERE user_id = c.chat_from)
						ELSE ''
					 END AS fullname,
					 CASE
						WHEN c.chat_from = ".$user_id." THEN (SELECT  count(id) FROM fx_chats_text WHERE text_from = c.chat_to and text_to = ".$user_id." and text_status = 0)
						WHEN c.chat_from != ".$user_id." THEN (SELECT  count(id) FROM fx_chats_text WHERE text_from = c.chat_from and text_to = ".$user_id." and text_status = 0)
						ELSE ''
						END AS new_chats,
					 (SELECT concat(text_from,'[^]',text_to,'[^]',text_content,'[^]',text_date_time) as det FROM fx_chats_text WHERE chat_id = c.chat_id order by text_date_time desc limit 1) as last_chat
					 FROM `fx_chats` c 
					 WHERE (c.chat_from = ".$user_id." or c.chat_to = ".$user_id.") order by chat_date_time desc";  
 		$query   = $this->db->query($usr_qry); 
		if ($query->num_rows() > 0){
			return $query->result();
		} 
	}
 function chat_text_details($user1,$user2,$order_by)
	{
		$qry      = "SELECT ct.*,ad1.fullname as from_user_name,ad2.fullname as to_user_fullname
					 FROM `fx_chats_text` ct
					 left join fx_account_details as ad1 on ad1.user_id = ct.text_from
					 left join fx_account_details as ad2 on ad2.user_id = ct.text_to
					 where (ct.text_from = ".$user1." and ct.text_to = ".$user2.") OR 
					 (ct.text_from = ".$user2." and ct.text_to = ".$user1." )"; 
		if($order_by == 1){ $qry .= "order by ct.id asc";}			 
		if($order_by == 2){ $qry .= "order by ct.id asc";}	 
  		$query   = $this->db->query($qry); 
		if ($query->num_rows() > 0){
			return $query->result_array();
		} 
	}	
	
 function check_already_chat($user1,$user2)
	{
		$usr_qry = "SELECT  chat_id from fx_chats WHERE (chat_from = ".$user1." and chat_to = ".$user2.") OR (chat_from = ".$user2." and chat_to = ".$user1.")";  
 		$query   = $this->db->query($usr_qry); 
		if ($query->num_rows() > 0){
			$chat = $query->result_array();
			return $chat[0]['chat_id'];
		} 
	}	
	 
function inser_chat($data,$sts)
	{ 
	    if($sts == 1){
	    	$this->db->insert('fx_chats',$data);
			return $this->db->insert_id();
		}else if($sts == 2){
			$this->db->update('fx_chats',$data,array('chat_id'=>$data['chat_id']));
		}
	}		 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	
}

/* End of file model.php */