<template>
    <v-layout fluid>
      <v-card>
        <v-card-title>
          <h1 class="display-3">Списък с пациенти:</h1>
        </v-card-title>
        <v-card-text>
          <v-spacer></v-spacer>
          <v-text-field
            v-model="search"
            append-icon="search"
            label="Търси по Име, Фамилия или срички по някое от двете."
            single-line
          ></v-text-field>
          <v-data-table
            :headers="headers"
            :items="filteredList"
            rows-per-page-text="Записи на страница"
            :loading="this.loading"
          >
            <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>

            <template slot="items" slot-scope="props">
              <td @click="openPatient(props.item.pid, props.item)" style="font-size: 18px">
                <span class="text-lg-left">
                    {{ props.item.fullName }}
                </span>
              </td>

            </template>
            <template slot="no-data" v-if="!this.loading">
              <v-alert slot="no-results" :value="true" color="error" icon="warning">
                Няма съвпадения от това написаното "{{ search }}"! Търсенето разпонзава само букви от латиница!
              </v-alert>
            </template>

          </v-data-table>
        </v-card-text>
      </v-card>
    </v-layout>
</template>

<script>
  import axios from 'axios'
  export default {
    name: 'list',
    data() {
      return {
        patientListSource: [],
        search: '',
        headers: [
          {
            text: 'Име и Фамилия',
            value: false
          }
        ],
        loading: false,
        items: [],

      }
    },

    async mounted()  {
      this.loading = true

      axios.get('/api/patients/')
        .then(
          (data) => {
            this.patientListSource = data.data
            this.loading = false
            // setTimeout(() => {
            //   this.patientListSource = data.data
            //   this.loading = false
            // },300)
          }

        )
        .catch(
          (er) => console.log("error")
        )
    },
    methods: {
      openPatient(id, item) {
        this.$router.push({ name: 'patient-show', params: { id: id, patientName: item.fullName }});

        this.$store.commit('setCurrentPatient', item.fullName)
      }
    },
    computed: {
      filteredList() {

        return this.patientListSource.filter(patient => {

          return patient.fullName.toLowerCase().includes(this.search.toLowerCase())
        })

      }
    }
  }
</script>

<style scoped>

</style>
