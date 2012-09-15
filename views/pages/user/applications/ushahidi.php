<div class="col_12 deployments">
	<div class="settings-toolbar">
		<p class="button-blue button-small create">
			<a href="#" class="modal-trigger"><span class="icon"></span><?php echo __("Add Deployment"); ?></a>
		</p>
	</div>
	<div class="alert-message blue" style="display: none;">
		<p>
			<strong><?php echo __("No deployments"); ?></strong>
			<?php echo __('You can add an Usahidi deployment to push drops to by selecting the "Add Deployment" button above'); ?>
		</p>
	</div>
</div>

<script type="text/template" id="add-deployment-dialog-template">
	<article class="modal">
		<hgroup class="page-title cf">
			<div class="page-h1 col_9">
				<h1><?php echo __("Add Deployment"); ?></h1>
			</div>
			<div class="page-actions col_3">
				<h2 class="close">
					<a href="#"><span class="icon"></span><?php echo __("Close"); ?></a>
				</h2>
			</div>
		</hgroup>
		<div class="modal-body">
			<div class="alert-message blue" style="display:none;">
				<p><?php echo __("The deployment has been successfully saved"); ?></p>
			</div>

			<?php echo Form::open(); ?>
			<article class="container base">
				<section class="property-parameters">
					<div class="parameter">
						<label for="deployment_name">
							<p class="field"><?php echo __("Deployment Name"); ?></p>
							<input type="text" name="deployment_name" value="<%= deployment_name %>" />
						</label>
					</div>
					<div class="parameter">
						<label for="deployment_url">
							<p class="field"><?php echo __("Deployment URL"); ?></p>
							<input type="text" name="deployment_url" value="<%= deployment_url %>" />
						</label>
					</div>
					<div class="parameter">
						<label for="deployment_token_key">
							<p class="field"><?php echo __("Token Key"); ?></p>
							<input type="text" naem="deployment_token_key" value="<%= deployment_token_key %>" />
						</label>
					</div>
					<div class="parameter">
						<label for="deployment_token_secret">
							<p class="field"><?php echo __("Token Secret"); ?></p>
							<input type="text" naem="deployment_token_secret" value="<%= deployment_token_secret %>" />
						</label>
					</div>
				</section>
			</article>
			<p class="button-blue">
				<a href="#"><?php echo __("Save"); ?></a>
			</p>
			<?php echo Form::close(); ?>
		</div>
	</article>
</script>

<script type="text/template" id="deployment-list-item-template">
	<header class="cf">
		<a href="#" class="remove-large" title="<?php echo __("Delete"); ?>">
			<span class="icon"></span>
			<span class="nodisplay"><?php echo __("Remove"); ?></span>
		</a>
		<p class="actions">
			<p class="button-blue button-small edit">
				<a href="#" title="<?php echo __("Edit the settings for this deployment"); ?>"><?php echo __("Edit"); ?></a>
			</p>
		</p>
		<div class="property-title">
			<a href="#" class="avatar-wrap"><img src=""></a>
			<h1><%= deployment_name %></h1>
		</div>
	</header>
</script>

<script type="text/javascript">
$(function(){

	var Deployment = Backbone.Model.extend();
	var DeploymentsList = Backbone.Collection.extend({
		model: Deployment,
		url: "<?php echo $action_url; ?>"
	});
	
	// View for the add deployment dialog
	var AddDeploymentModal = Backbone.View.extend({

		tagName: "article",
		
		className: "modal",
		
		template: _.template($("#add-deployment-dialog-template").html()),
		
		initialize: function() {
			this.isSaving = false;			
		},
		
		events: {
			"click p.button-blue a": "save"
		},
		
		save: function(e) {
			if (!this.isSaving) {
				this.isSaving = true;
				this.$("input").attr("readonly", true);
				
				// Data to be submitted for saving
				var deploymentData = {
					deployment_name: this.$("#deployment_name").val(),
					deployment_url: this.$("#deployment_url").val()
				};
				
				var context = this;
				this.model.save(deploymentData, {
					wait: true,
					
					success: function(model, response){
						// Show success message
						context.$el("div.blue").fadeIn();
						
						// Trigger a click on the close button
						context.$("h2.close a").trigger("click");
					},
					
					error: function(model, response){
						// Show error message
						
						// Make the input fields readonly
						context.$("input").removeAttr("readonly");
						context.isSaving = false;					
					}
				});
			}
			return false;
		},
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		}
	});
	
	// View for listing a single deployment on the UI
	var DeploymentView = Backbone.View.extend({
		tagName: "article",
		
		className: "container base",
		
		template: _.template($("#deployment-list-item-template").html()),
		
		events: {
			"click a.remove-large": "confirmDelete",
			"click p.edit a": "edit",
		},
		
		confirmDelete: function(e) {
			new ConfirmationWindow("Remove this deployment?", this.delete, this).show();
			return false;
		},
		
		delete: function(e) {
			var view = this;
			this.model.destory({
				wait: true,
				success: function(response) {
					view.$el.fadeOut("slow");
				},
				error: function(repsonse) {
					
				}
			});
			return false;
		},
		
		// Show the deployment settings in edit mode
		edit: function(e) {
			// Display the dialog
			var view = new AddDeploymentModel({model: this.model});
			modalShow(view.render().el);
			return false;
		},
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		}
	});
	
	// Initialize the deployments listing
	var deploymentsList = new DeploymentsList();
	
	// The deployments app
	var DeploymentsControl = Backbone.View.extend({

		el: "div.deployments",
		
		initialize: function() {
			deploymentsList.on("reset", this.addDeployments, this);
			deploymentsList.on("add", this.addDeployment, this);
			
			// Toggle display of the "No deployments message"
			deploymentsList.on("reset", this.isEmpty, this);
			deploymentsList.on("add", this.isEmpty, this);
			deploymentsList.on("remove", this.isEmpty, this);
		},
		
		events: {
			"click .settings-toolbar p.create a": "showAddDeploymentModal",
		},
		
		addDeployment: function(deployment) {
			var view = new DeploymentView({model: deployment});
			this.$el.append(view.render().el);
		},
		
		addDeployments: function() {
			deploymentsList.each(this.addDeployment, this);
		},
		
		// Callback to verify whether the deployments collection is empty
		isEmpty: function() {
			if (deploymentsList.length) {
				this.$(".alert-message").fadeIn();
			} else {
				this.$(".alert-message").fadeOut();
			}
		},
		
		// Display the "Add Deployment" modal dialog
		showAddDeploymentModal: function(e) {
			var deployment = new Deployment({
				deployment_name: null,
				deployment_url: null,
				deployment_token_key: null,
				deployment_token_secret: null
			});
			var view = new AddDeploymentModal({model: deployment});
			modalShow(view.render().el);
			return false;
		},
	});
	
	var deploymentsControl = new DeploymentsControl();
	deploymentsList.reset(<?php echo $deployments; ?>);
});
</script>