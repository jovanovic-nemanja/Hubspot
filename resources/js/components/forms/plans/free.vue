<template>
  <div>
    <div class="row sr-forms">
      <div class="col">
        <label for="portal-limit">Portal Limit</label>
        <input
          id="portal-limit"
          :class="{ 'is-invalid': form.errors.has('portal_limit') }"
          type="number"
          class="form-control"
          v-model="form.portal_limit"
        />
      </div>
      <div class="col">
        <label for="user-limit">User Limit</label>
        <input
          id="user-limit"
          :class="{ 'is-invalid': form.errors.has('user_limit') }"
          type="number"
          class="form-control"
          v-model="form.user_limit"
        />
      </div>
    </div>
    <div class="row sr-forms">
      <div class="col">
        <label for="module-limit">Module Limit</label>
        <input
          id="module-limit"
          :class="{ 'is-invalid': form.errors.has('module_limit') }"
          type="number"
          class="form-control"
          v-model="form.module_limit"
        />
      </div>
      <div class="col">
        <label for="limit-free">Allow Only Free Modules</label>
        <select
          name="limit-free"
          :class="{ 'is-invalid': form.errors.has('is_free_module') }"
          id="limit-free"
          class="form-control"
          v-model="form.is_free_module"
        >
          <option disabled value>--- Please Select ---</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
      </div>
    </div>
    <div class="row sr-forms">
      <div class="col">
        <label for="hidden-plan">Hidden Plan</label>
        <select
          name="hidden-plan"
          :class="{ 'is-invalid': form.errors.has('is_hidden_plan') }"
          id="hidden-plan"
          class="form-control"
          v-model="form.is_hidden_plan"
        >
          <option disabled value>--- Please Select ---</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
      </div>
      <div class="col">
        <label for="custom-logo">Allow Custom Logo</label>
        <select
          name="custom-logo"
          :class="{ 'is-invalid': form.errors.has('custom_logo') }"
          id="custom-logo"
          class="form-control"
          v-model="form.custom_logo"
        >
          <option disabled value>--- Please Select ---</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
      </div>
    </div>
    <div class="row sr-forms">
      <div class="col">
        <label for="status">Status</label>
        <select
          name="status"
          :class="{ 'is-invalid': form.errors.has('status') }"
          id="status"
          class="form-control"
          v-model="form.status"
        >
          <option disabled value>--- Please Select ---</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="col"></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    form: {
      type: Object
    }
  }
};
</script>