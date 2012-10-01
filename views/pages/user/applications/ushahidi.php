<div class="col_12 deployments">
	<div class="settings-toolbar">
		<p class="button-blue button-small create">
			<a href="#" class="modal-trigger"><span class="icon"></span><?php echo __("Add Deployment"); ?></a>
		</p>
	</div>
	<div class="alert-message blue" style="display: none;">
		<p>
			<strong><?php echo __("No deployments"); ?></strong>
			<?php echo __('You can add an Ushahidi deployment by selecting the "Add Deployment" button above'); ?>
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
			<div class="alert-message red" style="display:none;">
				<p></p>
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
							<input type="text" name="token_key" value="<%= token_key %>" />
						</label>
					</div>
					<div class="parameter">
						<label for="deployment_token_secret">
							<p class="field"><?php echo __("Token Secret"); ?></p>
							<input type="text" name="token_secret" value="<%= token_secret %>" />
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
	<article class="container base">
		<header class="cf">
			<a href="#" class="remove-large" title="<?php echo __("Delete"); ?>">
				<span class="icon"></span>
				<span class="nodisplay"><?php echo __("Delete"); ?></span>
			</a>
			<div class="actions">
				<p class="button-blue button-small edit">
					<a href="#" title="<?php echo __("Edit the settings for this deployment"); ?>">
						<?php echo __("Edit"); ?>
					</a>
				</p>
			</div>
			<div class="property-title">
				<h1><%= deployment_name %></h1>
			</div>
		</header>
	</article>
</script>

<script type="text/javascript">
$(function(){

	var Deployment = Backbone.Model.extend();
	var DeploymentsList = Backbone.Collection.extend({
		model: Deployment,
		url: "<?php echo $action_url; ?>"
	});

	// Initialize the deployments listing
	var deploymentsList = new DeploymentsList();
	
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
				// Hide any error messages
				this.$("div.alert-message").hide();
				
				this.$("input").attr("readonly", true);

				// Data to be submitted for saving
				var deploymentData = {};
				this.$('input[type="text"]').each(function(i, field){
					deploymentData[$(field).attr("name")]  = $(field).val();
				});

				// Show loading icon
				var loadingMessage = window.loading_message.clone();
				var submitButton = this.$("p.button-blue");
				this.$("p.button-blue").replaceWith(loadingMessage);

				var context = this;

				var options = {
					wait: true,

					success: function(model, response){
						// Show success message
						context.$("div.blue").fadeIn();
						context.isSaving = false;

						context.$(loadingMessage).replaceWith(submitButton);

						// Trigger a click on the close button
						setTimeout(function() { context.$("h2.close a").trigger("click"); }, 1200);
					},
					
					error: function(model, response){
						// Show error message
						context.$("div.red p").html(response.responseText);
						context.$("div.red").fadeIn();

						// Make the input fields readonly
						context.$("input").removeAttr("readonly");
						context.isSaving = false;

						// Show the save button
						context.$(loadingMessage).replaceWith(submitButton);
					}
				};
				
				if (this.model.get("id") === undefined) {
					deploymentsList.create(deploymentData, options);
				} else {
					this.model.save(deploymentData, options);
				}
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
		
		className: "item cf",
		
		template: _.template($("#deployment-list-item-template").html()),
		
		events: {
			"click a.remove-large": "confirmDelete",
			"click p.edit a": "edit",
		},
		
		confirmDelete: function(e) {
			new ConfirmationWindow("Remove deployment?", this.delete, this).show();
			return false;
		},
		
		delete: function(e) {
			var view = this;
			this.model.destroy({
				wait: true,
				success: function(response) {
					view.$el.fadeOut("slow");
				},
			});
			return false;
		},
		
		// Show the deployment settings in edit mode
		edit: function(e) {
			// Display the dialog
			var view = new AddDeploymentModal({model: this.model});
			modalShow(view.render().el);
			return false;
		},
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		}
	});
		
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
		isEmpty: function(deployment) {
			if (!deploymentsList.length) {
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