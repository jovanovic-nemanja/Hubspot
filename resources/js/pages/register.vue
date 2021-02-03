<template>
	<div class="setup-view">
		<!-- Login Form -->
		<div class="container">
			<div class="row">
				<div class="col-md-6 offset-md-3">
					<div class="setup-form-container">
						<img src="/images/icon-sr-dark.svg" alt="Sprocket Rocket Icon" />
						<h2>Setup Your Account</h2>
						<form @submit.prevent="registerLogin" @keydown="form.onKeydown($event)">
							<div class="form-group">
								<label for="inputName">Name</label>
								<input
									v-model="form.name"
									type="name"
									name="name"
									class="form-control"
									id="inputName"
								/>
							</div>
							<div class="form-group">
								<label for="InputPassword">Password</label>
								<input
									v-model="form.password"
									type="password"
									name="password"
									class="form-control"
									id="inputPassword"
								/>
							</div>
							<v-button :loading="form.busy" type="submit" class="btn btn-primary">{{
								$t("setup")
							}}</v-button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import Vue from "vue";
import VueRouter from "vue-router";
import Form from "vform";
import axios from "axios";
import { mapGetters } from "vuex";

export default {
	middleware: "guest",

	layout: "basic",

	metaInfo() {
		return { title: this.$t("home") };
	},

	data: () => ({
		title: window.config.appName,
		form: new Form({
			name: "",
			password: "",
			token: ""
		}),
		loginForm: new Form({
			email: "",
			password: ""
		})
	}),

	created() {
		this.form.token = this.$route.query.token;
	},

	methods: {
		async registerLogin() {
			// Submit the form.
			const registerData = await this.form.post("/api/v1/users/accept");

			this.loginForm.password = this.form.password;
			this.loginForm.email = registerData.data.user.email;

			const { data } = await this.loginForm.post("/api/login");

			// Save the token.
			this.$store.dispatch("auth/saveToken", {
				token: data.token,
				remember: this.remember
			});

			// Fetch the user.
			await this.$store.dispatch("auth/fetchUser");

			if (data.token) {
				const loggedUser = await axios.get("/api/v1/users/auth");

				if (loggedUser.data.agencies.portals_count > 0) {
					this.$router.push({ name: "portals-main" });
				} else {
					this.$router.push({ name: "portals-start" });
				}
			}
		}
	},

	computed: mapGetters({
		authenticated: "auth/check"
	})
};
</script>
