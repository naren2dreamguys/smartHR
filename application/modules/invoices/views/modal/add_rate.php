<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button> 
			<h4 class="modal-title"><?=lang('new_tax_rate')?></h4>
		</div>
		<?php $attributes = array('class' => 'bs-example form-horizontal'); echo form_open(base_url().'invoices/tax_rates/add',$attributes); ?>
			<div class="modal-body">
				<div class="form-group">
					<label class="col-lg-4 control-label"><?=lang('tax_rate_name')?> <span class="text-danger">*</span></label>
					<div class="col-lg-8">
						<input type="text" class="form-control" required placeholder="<?=lang('vat')?>" name="tax_rate_name">
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-4 control-label"><?=lang('tax_rate_percent')?> <span class="text-danger">*</span></label>
					<div class="col-lg-8">
						<input type="text" class="form-control money" required placeholder="12" name="tax_rate_percent">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="#" class="btn btn-danger" data-dismiss="modal"><?=lang('close')?></a> 
				<button type="submit" class="btn btn-success"><?=lang('save_changes')?></button>
			</div>
		</form>
	</div>
</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js" type="text/javascript"></script>
<script>
	$(function() {
		$('.money').maskMoney();
	})
</script>