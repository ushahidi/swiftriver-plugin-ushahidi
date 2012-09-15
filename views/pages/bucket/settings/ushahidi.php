<?php echo Form::open(); ?>
	<article class="container base">
		<header class="cf">
			<div class="property-title"><h1><?php echo __("Deployment"); ?></h1></div>
		</header>
		<section class="property-parameters">
			<div class="parameter">
				<label for="deployment_id">
					<p class="field"><?php echo __("Ushahidi Deployment"); ?></p>
					<select name="deployment_id" id="deployment_id"></select>
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
					<select name="deployent_category_id" id="deployment_category_id"></select>
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
	<div class="settings-toolbar"></div>
<?php echo Form::close(); ?>

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
		url: "<?php echo $fetch_url; ?>"
	});
	
	// Deploymet categories
	var DeploymentCategory = Backbone.Model.extend();
	var DeploymentCategoryList = Backbone.Collection.extend({
		model: DeploymentCategory;	
	});
	
	// Initialize the deployment listing and the category list
	// for the currently selected deployment
	var deploymentsList = new DeploymentList();
	var deploymentCategories = new DeploymentCategoryList();
	
	var DeploymentView = Backbone.View.extend({

		tagName: "option",
		
		template: _.template($("#deployment-template").html()),
		
		render: fuction() {
			this.$el.html(this.template(this.model.toJSON()));
			this.$el.attr("value", this.model.get("id"));
			return this;
		}
		
	});
	
	// Renders a single category item
	var DeploymentCategoryView = Backbone.View.extend({
		tagName: "option",
		
		template: _.template($("#deployment-category-template").html()),
		
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			this.$el.attr("value", this.model.get("deployment_category_id"));
			return this;
		}
	});

	// Initializes the controls + data for this view
	var DeploymentPushView = Backbone.View.extend({
		
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
			$("#deployment_id").append(view.render().el);
		},
		
		addDeployments: function() {
			deploymentsList.each(this.addDeployment, this);
		},
		
		addDeploymentCategory: function(category) {
			var view = new DeploymentCategoryView({model: category});
			$("#deployment_category_id").append(view.render().el);
		},
		
		addDeploymentCategories: function() {
			// Clear the current list of categories
			$("#deployment_category_id").html("");
			if (deploymentCategories.length() > 0) {
				deploymentCategories.each(this.addDeploymentCategory, this);
			}
		},
		
		// Callback for the onchange event against the deployment list
		fetchCategories: function(e) {
			deploymentCategories.fetch();
		},
	});
	
	var deploymentPushView = new DeploymentPushView();
	deploymentsList.reset(<?php echo $deployments_list; ?>);
	deploymentCategories.reset(<?php echo $deployment_categories; ?>);
});
</script>