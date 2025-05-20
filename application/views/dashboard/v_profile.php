<div class="content-wrapper">
	<section class="content-header">
		<h1>Profile
			<small>Update Profile Pengguna</small>
		</h1>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-lg-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title"> Update Profile</h3>
					</div>
					<div class="box-body">
						<?php
						if(isset($_GET['alert'])){
							if($_GET['alert']=="sukses"){
								echo "<div class='alert alert-success'>Profile telah diupdate !</div>";
							}
						}
						?>

						<?php foreach($profile as $p){ ?>
							<form method="post" action="<?php echo base_url('dashboard/profile_update')?>">
								<div class="box-body">
									<div class="form-group">
										<label>Nama</label>
										<input type="text" name="nama" class="form-control" placeholder="Masukkan nama.. "value="<?php echo $p->pengguna_nama; ?>">
										<?php echo form_error('nama');?>
									</div>
									<div class="form-group">
										<label>Email</label>
										<input type="text" name="email" class="form-control" placeholder="Masukkan Email.. "value="<?php echo $p->pengguna_email; ?>">
										<?php echo form_error('email');?>
									</div>
								</div>
								<div class="box-footer">
									<input type="submit" class="btn btn-success" value="Update">
								</div>

							</form>

						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>