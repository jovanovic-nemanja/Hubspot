import axios from "axios";
import * as types from "../mutation-types";

// state
export const state = {
  status: null
};

// getters
export const getters = {
  status: state => state.status,
  check: state => state.user !== null
};

// mutations
export const mutations = {
  [types.FETCH_AGENCY_BILLING_SUCCESS](state, { status }) {
    state.status = status;
  }
};

// actions
export const actions = {
  async fetchAgencyBilling({ commit }) {
    try {
      const { data } = await axios.get("/api/v1/agencies/check-billing");

      commit(types.FETCH_AGENCY_BILLING_SUCCESS, {
        status: data.billing_status
      });
    } catch (e) {}
  }
};
