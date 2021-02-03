<template>
  <div>
    <alert :options.sync="options" :show.sync="showAlert" @close="closeAlert" />
    <div class="action-panel-content">
      <div class="action-container">
        <div class="action-heading" v-if="!isAddPaymentMethod">
          <h2>Billing Information</h2>
        </div>
        <div class="action-heading" v-if="isAddPaymentMethod">
          <h2>Payment Method</h2>
        </div>
        <form v-if="!subscribeAsPro" id="billing-information" @submit.prevent="submitBilling">
          <div v-if="!isAddPaymentMethod">
            <div class="row sr-forms">
              <div class="col plan">
                <label class="billing-plan" for="billing-plan">Plan</label>
              </div>
            </div>
            <div class="row sr-forms custom-box-radio">
              <div v-for="plan in plans" :key="plan.id">
                <input
                  :id="plan.id"
                  type="radio"
                  name="billing-plan"
                  v-model="form.plan_id"
                  v-bind:value="plan.id"
                  @click="selectPlan(plan)"
                  :checked="plan.id == form.plan_id"
                />
                <label
                  class="box-plan"
                  :class="{ unlimited: plan.is_hidden_plan == 'yes' }"
                  :for="plan.id"
                >
                  <span class="plan-headline" v-if="plan.price == 0 || plan.price == null">Free</span>
                  <span class="plan-headline" v-if="plan.price > 0 && plan.type != 'fixed'">
                    {{ plan.currency_symbol }} {{ plan.price | numFormat(0) }} /
                    <span
                      v-if="plan.interval == 'monthly'"
                    >mo</span>
                    <span v-else>year</span>
                  </span>
                  <span
                    class="plan-headline"
                    v-if="plan.price > 0 && plan.type == 'fixed'"
                  >{{ plan.currency_symbol }} {{ plan.price | numFormat(0) }}</span>
                  <span class="plan-includes">
                    <strong v-if="plan.type == 'fixed'">{{ plan.name }} - One-off Payment</strong>
                    <strong v-if="plan.type == 'paid'">{{ plan.name }} - Subscription</strong>
                    <strong v-if="plan.type == 'free'">{{ plan.name }}</strong>
                    <br />
                    {{ plan.portal_limit }}
                    <span v-if="plan.portal_limit == 1">Portal</span>
                    <span v-else>Portals</span>
                    <br />
                    {{ plan.user_limit }}
                    <span v-if="plan.user_limit == 1">User</span>
                    <span v-else>Users</span>
                    <br />
                    <span v-if="plan.module_limit == 999">Unlimited Modules</span>
                    <span v-else>{{ plan.module_limit }} Modules</span>
                  </span>
                </label>
              </div>
            </div>
          </div>

          <div class="row sr-forms" v-if="plan_type !== 'free' && !isAddPaymentMethod">
            <div class="col plan">
              <label class="billing-plan" for="billing-plan">Payment Method</label>
            </div>
          </div>
          <div class="row" v-if="plan_type !== 'free' && !isAddPaymentMethod">
            <div class="col-md-8">
              <div v-for="paymentMethod in paymentMethods" :key="paymentMethod.id">
                <span
                  v-if="paymentMethodSelected !== paymentMethod.id"
                  class="pr-4"
                >{{ paymentMethod.brand | capitalize }}</span>
                <span
                  v-if="paymentMethodSelected === paymentMethod.id"
                  class="pr-4"
                >{{ paymentMethod.brand | capitalize }}</span>
                <span class="pr-4">•••• {{ paymentMethod.last_four }}</span>
                <span class="pr-4">{{ paymentMethod.exp_month }} / {{ paymentMethod.exp_year }}</span>
                <a
                  class="editor-link"
                  href="#"
                  @click="actionUpdatePaymentMethod(paymentMethod)"
                >Update</a>
              </div>
              <div class="row" v-if="plan_type !== 'free' && !isAddPaymentMethod">
                <div class="col-md-3 save-subscribe">
                  <button
                    v-if="paymentMethods.length === 0"
                    @click="actionPaymentMethod()"
                    class="btn btn-primary"
                  >Add</button>
                </div>
              </div>
            </div>
          </div>

          <div class="row sr-forms" v-if="isAddPaymentMethod">
            <div class="col">
              <label for="name">Name on Card</label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errorFormName }"
              />
            </div>
            <div class="col">
              <label for="email">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': errorFormEmail }"
              />
            </div>
          </div>
          <div class="row sr-forms" v-if="isAddPaymentMethod">
            <div class="col">
              <label for="country">Country or Region</label>
              <select
                name="country"
                v-model="form.country"
                id="country"
                class="form-control"
                :class="{ 'is-invalid': errorFormCountry }"
              >
                <option value>--- Please Select ---</option>
                <option value="AR">Argentina</option>
                <option value="AU">Australia</option>
                <option value="AT">Austria</option>
                <option value="BY">Belarus</option>
                <option value="BE">Belgium</option>
                <option value="BA">Bosnia and Herzegovina</option>
                <option value="BR">Brazil</option>
                <option value="BG">Bulgaria</option>
                <option value="CA">Canada</option>
                <option value="CL">Chile</option>
                <option value="CN">China</option>
                <option value="CO">Colombia</option>
                <option value="CR">Costa Rica</option>
                <option value="HR">Croatia</option>
                <option value="CU">Cuba</option>
                <option value="CY">Cyprus</option>
                <option value="CZ">Czech Republic</option>
                <option value="DK">Denmark</option>
                <option value="DO">Dominican Republic</option>
                <option value="EG">Egypt</option>
                <option value="EE">Estonia</option>
                <option value="FI">Finland</option>
                <option value="FR">France</option>
                <option value="GE">Georgia</option>
                <option value="DE">Germany</option>
                <option value="GI">Gibraltar</option>
                <option value="GR">Greece</option>
                <option value="HK">Hong Kong S.A.R., China</option>
                <option value="HU">Hungary</option>
                <option value="IS">Iceland</option>
                <option value="IN">India</option>
                <option value="ID">Indonesia</option>
                <option value="IR">Iran</option>
                <option value="IQ">Iraq</option>
                <option value="IE">Ireland</option>
                <option value="IL">Israel</option>
                <option value="IT">Italy</option>
                <option value="JM">Jamaica</option>
                <option value="JP">Japan</option>
                <option value="KZ">Kazakhstan</option>
                <option value="KW">Kuwait</option>
                <option value="KG">Kyrgyzstan</option>
                <option value="LA">Laos</option>
                <option value="LV">Latvia</option>
                <option value="LB">Lebanon</option>
                <option value="LT">Lithuania</option>
                <option value="LU">Luxembourg</option>
                <option value="MK">Macedonia</option>
                <option value="MY">Malaysia</option>
                <option value="MT">Malta</option>
                <option value="MX">Mexico</option>
                <option value="MD">Moldova</option>
                <option value="MC">Monaco</option>
                <option value="ME">Montenegro</option>
                <option value="MA">Morocco</option>
                <option value="NL">Netherlands</option>
                <option value="NZ">New Zealand</option>
                <option value="NI">Nicaragua</option>
                <option value="KP">North Korea</option>
                <option value="NO">Norway</option>
                <option value="PK">Pakistan</option>
                <option value="PS">Palestinian Territory</option>
                <option value="PE">Peru</option>
                <option value="PH">Philippines</option>
                <option value="PL">Poland</option>
                <option value="PT">Portugal</option>
                <option value="PR">Puerto Rico</option>
                <option value="QA">Qatar</option>
                <option value="RO">Romania</option>
                <option value="RU">Russia</option>
                <option value="SA">Saudi Arabia</option>
                <option value="RS">Serbia</option>
                <option value="SG">Singapore</option>
                <option value="SK">Slovakia</option>
                <option value="SI">Slovenia</option>
                <option value="ZA">South Africa</option>
                <option value="KR">South Korea</option>
                <option value="ES">Spain</option>
                <option value="LK">Sri Lanka</option>
                <option value="SE">Sweden</option>
                <option value="CH">Switzerland</option>
                <option value="TW">Taiwan</option>
                <option value="TH">Thailand</option>
                <option value="TN">Tunisia</option>
                <option value="TR">Turkey</option>
                <option value="UA">Ukraine</option>
                <option value="AE">United Arab Emirates</option>
                <option value="GB">United Kingdom</option>
                <option value="US">United States</option>
                <option value="UZ">Uzbekistan</option>
                <option value="VN">Vietnam</option>
              </select>
            </div>
            <div class="col"></div>
          </div>
          <div class="row sr-forms credit-info" v-if="isAddPaymentMethod">
            <div class="col">
              <label for="card-info">Card Information</label>
              <div id="card-element"></div>
            </div>
            <div class="col"></div>
          </div>
          <div class="row" v-if="isAddPaymentMethod">
            <div class="col-md-3 save-subscribe">
              <button
                v-if="isAddPaymentMethod && !isUpdatePaymentMethod"
                type="button"
                :disabled="isLoading"
                @click="addPaymentMethod"
                class="btn btn-primary"
              >Add</button>
              <button
                v-if="isAddPaymentMethod && isUpdatePaymentMethod"
                type="button"
                :disabled="isLoading"
                @click="updatePaymentMethod"
                class="btn btn-primary"
              >Update</button>
              <button
                :disabled="isLoading"
                @click="isAddPaymentMethod = false; isUpdatePaymentMethod = false"
                class="btn btn-secondary"
              >Cancel</button>
            </div>
          </div>
          <div v-if="isPlanChanges || isSuspend">
            <div v-if="!isUpdatePaymentMethod">
              <div class="row mt-4" v-if="paymentMethods.length > 0">
                <div class="col-md-3 save-subscribe">
                  <button
                    :disabled="isLoading"
                    type="submit"
                    class="btn btn-primary"
                  >{{ subscribe_button_state }}</button>
                </div>
                <div class="col-md-9 confirm-subscription-message">
                  <!-- Confirm subscription message -->
                </div>
              </div>
            </div>
          </div>
        </form>
        <div v-if="subscribeAsPro">
          <strong>You are currently on the Sprocket Rocket Pro plan.</strong>
          <br><br>
          Do you want to use Sprocket Rocket with multiple HubSpot Portals?
          <br>
          <a href="https://brandvious.clickfunnels.com/order-sprocket-rocket41466048" target="_blank">Buy Additional Portals</a>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import axios from "axios";
import Form from "vform";
import Alert from "../Alert";

export default {
  name: "Billings",

  computed: mapGetters({
    authUser: "auth/user",
    billingStatus: "billing/status"
  }),

  components: {
    Alert
  },

  filters: {
    capitalize: function(value) {
      if (!value) return "";
      value = value.toString();
      return value.charAt(0).toUpperCase() + value.slice(1);
    }
  },

  data: () => ({
    // Stripe
    stripeAPIToken: window.config.stripePK,
    stripe: "",
    elements: "",
    card: "",
    intentToken: "",
    paymentMethods: [],
    paymentMethodsLoadStatus: 0,
    paymentMethodSelected: "",
    selectedPlan: "",
    isPlanChanges: false,
    isLoading: false,
    isAddPaymentMethod: false,
    isUpdatePaymentMethod: false,
    isFixed: false,
    subscribeAsPro: false,
    errorFormName: false,
    errorFormEmail: false,
    errorFormCountry: false,
    subscribe_button_state: "Update Plan",

    plans: [],
    plan_type: "free",
    options: {},
    showAlert: false,
    form: new Form({
      name: "",
      email: "",
      country: "",
      plan_id: ""
    })
  }),

  methods: {
    async getPlans() {
      const response = await axios.get("/api/v1/plans");
      this.plans = response.data.data;
    },

    closeAlert() {
      this.options = {};
      this.showAlert = false;
    },

    actionPaymentMethod() {
      this.includeStripe(
        "js.stripe.com/v3/",
        function() {
          this.configureStripe();
        }.bind(this)
      );

      this.loadIntent();
      this.isAddPaymentMethod = true;
    },

    includeStripe(URL, callback) {
      let documentTag = document;
      let tag = "script";
      let object = documentTag.createElement(tag);
      let scriptTag = documentTag.getElementsByTagName(tag)[0];
      object.src = "//" + URL;
      if (callback) {
        object.addEventListener(
          "load",
          function(e) {
            callback(null, e);
          },
          false
        );
      }
      scriptTag.parentNode.insertBefore(object, scriptTag);
    },

    configureStripe() {
      this.stripe = Stripe(this.stripeAPIToken);

      this.elements = this.stripe.elements();
      this.card = this.elements.create("card");

      this.card.mount("#card-element");
    },

    loadIntent() {
      let self = this;

      axios
        .get("/api/v1/billings/setup-intent")
        .then(
          function(response) {
            this.intentToken = response.data;
          }.bind(this)
        )
        .catch(function(error) {
          self.showAlert = true;
          self.options.content = error.response.data.message;
          self.options.type = "danger";
        });
    },

    loadPaymentMethods() {
      let self = this;

      axios
        .get("/api/v1/billings/payment-methods")
        .then(
          function(response) {
            this.paymentMethods = response.data;

            this.paymentMethodsLoadStatus = 2;
          }.bind(this)
        )
        .catch(function(error) {
          self.showAlert = true;
          self.options.content = error.response.data.message;
          self.options.type = "danger";
        });
    },

    submitBilling() {
      this.showAlert = false;
      this.options = {};

      if (this.isFixed) {
        this.isLoading = true;
        this.subscribe_button_state = "Processing...";
        axios
          .put("/api/v1/billings/charge", {
            plan: this.form.plan_id,
            payment: this.paymentMethods[0].id
          })
          .then(
            function(response) {
              this.subscribe_button_state = "Pay";
              this.isLoading = false;
              this.showAlert = true;
              this.options.content = "Payment Success!";
              this.options.type = "success";
              this.loadIntent();

              setTimeout(() => {
                window.location.reload();
              }, 3000);
            }.bind(this)
          )
          .catch(err => {
            this.subscribe_button_state = "Pay";
            this.isLoading = false;
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
          });
      } else {
        if (this.selectedPlan.type == "free") {
          this.isLoading = true;
          this.subscribe_button_state = "Processing...";
          axios
            .put("/api/v1/billings/cancel-subscription", {
              plan: this.selectedPlan
            })
            .then(
              function(response) {
                this.subscribe_button_state = "Update Plan";
                this.isLoading = false;
                this.showAlert = true;
                this.options.content =
                  "Plan Updated! Your subscription will not renew at the end of your billing period.";
                this.options.type = "success";
                this.loadIntent();

                setTimeout(() => {
                  window.location.reload();
                }, 3000);
              }.bind(this)
            );
        } else {
          this.isLoading = true;
          this.subscribe_button_state = "Processing...";
          axios
            .put("/api/v1/billings/subscription", {
              plan: this.form.plan_id,
              payment: this.paymentMethods[0].id
            })
            .then(
              function(response) {
                this.subscribe_button_state = "Update Plan";
                this.isLoading = false;
                this.showAlert = true;
                this.options.content = "Plan Successfully Updated!";
                this.options.type = "success";
                this.loadIntent();

                setTimeout(() => {
                  window.location.reload();
                }, 3000);
              }.bind(this)
            )
            .catch(err => {
              this.subscribe_button_state = "Update Plan";
              this.isLoading = false;
              this.showAlert = true;
              this.options.content = err.response.data.message;
              this.options.type = "danger";
            });
        }
      }
    },

    selectPlan(plan) {
      this.selectedPlan = plan;
      this.plan_type = plan.type;

      if (plan.type == "fixed") {
        this.isFixed = true;
        this.subscribe_button_state = "Pay";
      } else {
        this.subscribe_button_state = "Update Plan";
      }

      if (this.selectedPlan.id != this.authUser.agencies.plans.id) {
        this.isPlanChanges = true;
      } else {
        this.isPlanChanges = false;
      }
    },

    addPaymentMethod() {
      this.errorFormName = false;
      this.errorFormEmail = false;
      this.errorFormCountry = false;
      this.showAlert = false;
      this.options = {};

      if (
        this.form.name !== "" &&
        this.form.email !== "" &&
        this.form.country !== ""
      ) {
        this.isLoading = true;
        this.stripe
          .confirmCardSetup(this.intentToken.client_secret, {
            payment_method: {
              card: this.card,
              billing_details: {
                name: this.form.name,
                email: this.form.email
              }
            }
          })
          .then(
            function(result) {
              if (result.error) {
                this.isLoading = false;
              } else {
                axios
                  .post("/api/v1/billings/payment-methods", {
                    payment_method: result.setupIntent.payment_method,
                    billing: this.form
                  })
                  .then(
                    function(result) {
                      this.form.zip = result.data.zip;
                      this.isAddPaymentMethod = false;
                      this.isUpdatePaymentMethod = false;
                      this.paymentMethodSelected = "";
                      this.isLoading = false;
                      this.loadPaymentMethods();
                      this.isPlanChanges = true;
                    }.bind(this)
                  )
                  .catch(err => {
                    console.log(err);
                    this.isLoading = false;
                    this.showAlert = true;
                    this.options.content = err.response.data.message;
                    this.options.type = "danger";
                  });
              }
            }.bind(this)
          );
      } else {
        if (this.form.name === "") {
          this.showAlert = true;
          this.options.content = "Name is required";
          this.options.type = "danger";
          this.errorFormName = true;
        }
        if (this.form.email === "") {
          this.showAlert = true;
          this.options.content = "Email is required";
          this.options.type = "danger";
          this.errorFormEmail = true;
        }
        if (this.form.country === "") {
          this.showAlert = true;
          this.options.content = "Country is required";
          this.options.type = "danger";
          this.errorFormCountry = true;
        }
      }
    },

    updatePaymentMethod() {
      this.errorFormName = false;
      this.errorFormEmail = false;
      this.errorFormCountry = false;
      this.showAlert = false;
      this.options = {};

      if (
        this.form.name !== "" &&
        this.form.email !== "" &&
        this.form.country !== ""
      ) {
        this.isLoading = true;

        axios
          .post("/api/v1/billings/remove-payment-methods", {
            id: this.paymentMethodSelected
          })
          .then(
            function(result) {
              this.stripe
                .confirmCardSetup(this.intentToken.client_secret, {
                  payment_method: {
                    card: this.card,
                    billing_details: {
                      name: this.form.name,
                      email: this.form.email
                    }
                  }
                })
                .then(
                  function(result) {
                    if (result.error) {
                      this.isLoading = false;
                    } else {
                      axios
                        .post("/api/v1/billings/payment-methods", {
                          payment_method: result.setupIntent.payment_method,
                          billing: this.form
                        })
                        .then(
                          function(result) {
                            this.form.zip = result.data.zip;
                            this.isAddPaymentMethod = false;
                            this.isUpdatePaymentMethod = false;
                            this.paymentMethodSelected = "";
                            this.isLoading = false;
                            this.loadPaymentMethods();
                          }.bind(this)
                        )
                        .catch(err => {
                          this.isLoading = false;
                          this.showAlert = true;
                          this.options.content = err.response.data.message;
                          this.options.type = "danger";
                        });
                    }
                  }.bind(this)
                );
            }.bind(this)
          )
          .catch(err => {
            this.isLoading = false;
            this.showAlert = true;
            this.options.content = err.response.data.message;
            this.options.type = "danger";
          });
      } else {
        if (this.form.name === "") {
          this.showAlert = true;
          this.options.content = "Name is required";
          this.options.type = "danger";
          this.errorFormName = true;
        }
        if (this.form.email === "") {
          this.showAlert = true;
          this.options.content = "Email is required";
          this.options.type = "danger";
          this.errorFormEmail = true;
        }
        if (this.form.country === "") {
          this.showAlert = true;
          this.options.content = "Country is required";
          this.options.type = "danger";
          this.errorFormCountry = true;
        }
      }
    },

    actionUpdatePaymentMethod(paymentMethod) {
      this.paymentMethodSelected = paymentMethod.id;
      this.actionPaymentMethod();
      this.isUpdatePaymentMethod = true;
    }
  },

  beforeMount: function() {
    this.form.plan_id = this.authUser.agencies.plans.id;
    this.plan_type = this.authUser.agencies.plans.type;

    if (this.plan_type == "fixed") {
      this.isFixed = true;
      this.subscribe_button_state = "Pay";
    } else {
      this.subscribe_button_state = "Update Plan";
    }

    this.isSuspend = this.authUser.agencies.status == "suspend" ? true : false;

    if (this.isAgencyAdmin && this.billingStatus == "suspend") {
      this.isSuspend = true;
    } else if (this.isUser && this.billingStatus == "suspend") {
      this.isSuspend = true;
    }

    if (
      this.authUser.agencies.paid_in_advance &&
      this.authUser.agencies.plans.type == "fixed"
    ) {
      this.subscribeAsPro = true;
    }

    if (this.authUser.agencies.billing == null) {
      this.form.name = this.authUser.name;
      this.form.email = this.authUser.email;
    } else {
      this.form.name = this.authUser.agencies.billing.name;
      this.form.email = this.authUser.agencies.billing.email;
      this.form.country = this.authUser.agencies.billing.country;
    }
  },

  mounted: function() {
    this.getPlans();
    this.loadPaymentMethods();
  }
};
</script>
