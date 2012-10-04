<div id="content" class="settings cf ushahidi">
	<div class="center">
		<div class="col_12">
			<?php if (isset($errors)): ?>
			<div class="alert-message red">
				<p><strong><?php __("Uh oh!"); ?></strong><?php echo $errros; ?></p>
			</div>
			<?php endif; ?>
			
			<?php if (isset($success) AND $success): ?>
			<div class="alert-message blue">
				<p>
					<strong><?php echo __("Success"); ?></strong>
					<?php 
					    echo __("The settings have been saved! The new settings will be used the next time drops are pushed to :name",
					        array(":name" => $push_settings->deployment->deployment_url));
					?>
				</p>
			</div>
			<?php endif; ?>
			
			<?php echo Form::open(); ?>
				<article class="container base">
					<header class="cf">
						<div class="property-title"><h1><?php echo __("Deployment"); ?></h1></div>
					</header>
					<section class="property-parameters">
						<div class="parameter">
							<label for="deployment_id">
								<p class="field"><?php echo __("Ushahidi Deployment"); ?></p>
								<select name="deployment_id" id="deployment_id">
									<option value="0"><?php echo __("--- Select Deployment ---"); ?></option>
								</select>
							</label>
						</div>
					</section>
				</article>
				<article class="container base">
					<header class="cf">
						<div class="property-title"><h1><?php echo __("Category"); ?></h1></div>
					</header>
					<section class="property-parameters">
						<div class="parameter">
							<label for="deployment_category_id">
								<p class="field"><?php echo __("Report Category"); ?></p>
								<select name="deployment_category_id" id="deployment_category_id"></select>
							</label>
						</div>
					</section>
				</article>
				<article class="container base">
					<header class="cf">
						<div class="property-title"><h1><?php echo __("Drop Count"); ?></h1></div>
					</header>
					<section class="property-parameters">
						<div class="parameter">
							<label for="push_drop_count">
								<p class="field"><?php echo __("No. of drops"); ?></p>
								<input type="text" name="push_drop_count" value="<?php echo $push_drop_count; ?>">
							</label>
						</div>
					</section>
				</article>
				<div class="save-toolbar">
					<p class="button-blue"><a href="#" onclick="submitForm(this);"><?php echo __("Save Changes"); ?></a></p>
				</div>
			<?php echo Form::close(); ?>
		</div>
	</div>
</div>

<script type="text/template" id="deployment-template">
	<%= deployment_name %>
</script>

<script type="text/template" id="deployment-category-template">
	<%= category_name %>
</script>

<script type="text/javascript">
$(function(){
	
	// Deployments
	var Deployment = Backbone.Model.extend();	
	var DeploymentList = Backbone.Collection.extend({
		model: Deployment,
	});
	
	// Deploymet categories
	var DeploymentCategory = Backbone.Model.extend();
	var DeploymentCategoryList = Backbone.Collection.extend({
		model: DeploymentCategory,
		url: "<?php echo $fetch_url; ?>"		
	});
	
	// Initialize the deployment listing and the category list
	// for the currently selected deployment
	var deploymentsList = new DeploymentList();
	var deploymentCategories = new DeploymentCategoryList();
	
	var DeploymentView = Backbone.View.extend({

		tagName: "option",
		
		template: _.template($("#deployment-template").html()),
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			this.$el.attr("value", this.model.get("id"));
			
			// Select the current deployment for the push
			<?php if ($push_settings->loaded()): ?>
			var activeDeploymentID = <?php echo $push_settings->deployment_id; ?>;
			if (activeDeploymentID == this.model.get("id")) {
				this.$el.attr("selected", "selected");
			}
			<?php endif; ?>
			return this;
		}

	});
	
	// Renders a single category item
	var DeploymentCategoryView = Backbone.View.extend({		
		tagName: "option",
		
		template: _.template($("#deployment-category-template").html()),
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			this.$el.attr("value", this.model.get("id"));
			
			// Select the current category for the push
			<?php if ($push_settings->loaded()): ?>
			var activeCategoryID = <?php echo $push_settings->deployment_category_id; ?>;
			if (activeCategoryID == this.model.get("id")) {
				this.$el.attr("selected", "selected");
			}
			<?php endif; ?>
			
			return this;
		}
	});

	// Initializes the controls + data for this view
	var DeploymentPushView = Backbone.View.extend({
		el: "div.ushahidi",
		
		initialize: function() {
			deploymentsList.on("add", this.addDeployment, this);
			deploymentsList.on("reset", this.addDeployments, this);
			
			deploymentCategories.on("add", this.addDeploymentCategory, this);
			deploymentCategories.on("reset", this.addDeploymentCategories, this);
		},
		
		events: {
			"change #deployment_id": "fetchCategories",
		},
		
		addDeployment: function(deployment) {
			var view = new DeploymentView({model: deployment});
			this.$("#deployment_id").append(view.render().el);
		},
		
		addDeployments: function() {
			deploymentsList.each(this.addDeployment, this);
		},
		
		addDeploymentCategory: function(category) {
			var view = new DeploymentCategoryView({model: category});
			this.$("#deployment_category_id").append(view.render().el);
		},
		
		addDeploymentCategories: function() {
			// Clear the current list of categories
			this.clearCategories();
			if (deploymentCategories.length > 0) {
				deploymentCategories.each(this.addDeploymentCategory, this);
			}
		},
		
		// Clears the categories dropdown
		clearCategories: function() {
			this.$("#deployment_category_id").html("");
		},
		
		// Callback for the onchange event against the deployment list
		fetchCategories: function(e) {
			var deploymentID = $(e.currentTarget).val();
			if (deploymentID > 0) {
				deploymentCategories.fetch({data: {id: deploymentID}});
			} else {
				this.clearCategories();
			}
			return false;
		},
	});
	
	var deploymentPushView = new DeploymentPushView();
	deploymentsList.reset(<?php echo $deployments_list; ?>);
	deploymentCategories.reset(<?php echo $deployment_categories; ?>);
});
</script>