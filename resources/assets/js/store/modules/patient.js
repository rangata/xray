import * as types from '../mutation-types'

export const state = {
  patient: null,
  imageData: null
}

export const mutations = {
  setCurrentPatient (state, payload) {
    state.patient = payload
  },
  setCurrentImage (state, payload) {
    state.imageData = payload
  }
}
export const actions = {
}

// getters
export const getters = {
  patientName: state => state.patient,
  currentImageData: state => state.imageData

  // patientName: state => {
  //   //   return { ...state }
  //   // }
}
