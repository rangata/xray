<template>

  <v-card color="blue blue-3" primary-title>
    <v-card-title>
      <h1>
        {{ patientName }}
      </h1>
    </v-card-title>
    <v-card-text style="background-color: white">
      <v-container
        fluid
        grid-list-lg
      >
        <v-card color="black darken-1" style="color: white">
          <v-progress-linear :indeterminate="true" color="lime lime-1" v-if="loading"></v-progress-linear>
          <v-card-title>
            <h3>
              Изследвания:
            </h3>
          </v-card-title>

          <v-card-text style="background-color: white" >
            <v-card v-for="(study,key) of allData.studies">
              <v-card-title>
               <span>
                 Дата на изследването:
                  {{ normalizeDate(allData.instances[key].MainDicomTags.InstanceCreationDate) }}
               </span>
              </v-card-title>
              <v-card-text>
                <v-img
                  class="white--text"
                  contain
                  :src="allData.instancePreviews[key]"
                >
                  <!--<v-container fill-height fluid>-->
                    <!--<v-layout fill-height>-->
                      <!--<v-flex xs12 align-end flexbox>-->
                        <!--<span class="headline">Snimka</span>-->
                      <!--</v-flex>-->
                    <!--</v-layout>-->
                  <!--</v-container>-->
                </v-img>
              </v-card-text>
              <v-card-actions>
                <v-btn color="success" block v-on:click="goToInstance(allData.instances[key].ID, allData.instancePreviews[key])">
                  Преглед
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-card-text>
        </v-card>
      </v-container>
    </v-card-text>
  </v-card>

</template>

<script>
  import axios from 'axios'

  export default {
    name: 'patient-show',

    data() {
      return {
        allData: [],
        patientInfo: {},
        patientName: this.$store.getters.patientName ,
        // patientStudies: '',
        // studies: [],
        // series: [],
        // instances: [],
        // images: [],
        loading: true,
        width: this.$vuetify.breakpoint.width - 150 + 'px',
        height: this.$vuetify.breakpoint.height - 150    + 'px'
      }
    },
    async mounted () {
      axios.get('/api/patients/' + this.$route.params.id)
        .then(
          (data) => {
            this.images = data.data.instancePreviews
            this.allData = data.data
          }
        )
        .then(
          () => {
            this.loading = false
          }
        )
        .catch(
          (error) => {
            console.error(error)
          }
        )
      // console.log(this.$store.state.patient)
      // this.patientName = this.$store.state.patient.fullName
      // this.patientStudies = this.$store.state.patient.studies

      // console.log(this.patientStudies)
      // let lteObj = [];
      //
      // this.patientStudies.forEach((studie, key) => {
      //   let skey = key
      //   axios.get('/api/studies/' + studie)
      //     .then(
      //       (data) => {
      //         // this.studies.push(data.data);
      //         lteObj = data.data
      //         lteObj['instanc'] = [];
      //         // lteObj.instanc.push('aa');
      //
      //         axios.get('/api/studies/' + lteObj.ID + '/instances')
      //           .then(
      //             (data) => {
      //               data.data.forEach(id => {
      //                 axios.get('/api/instances/' + id.ID + '/preview', {responseType: 'arraybuffer'})
      //                   .then(
      //                     (data) => {
      //                       this.studies[skey]['instanc'] = 'data:image/png;base64, ' + new Buffer.from(data.data, 'binary').toString('base64')
      //                     }
      //                   )
      //
      //               })
      //             }
      //           )
      //         this.studies.push(lteObj)
      //       }
      //
      //     )
      //     .catch(
      //       (err) => {
      //         if(err.response.status === 500) {
      //           this.$swal({
      //             confirmButtonColor: 'green',
      //             title: "Сървърът не отговаря!",
      //             text: 'Грешка № '+ err.response.status + '' +
      //               'За да сте получили тази грешка, то някои от следните събития са настъпили в последствие:' +
      //               '1. Електричеството във ФДМ е прекъснало.',
      //             type: "error"
      //           });
      //         }
      //       }
      //     )
      //
      //   this.loading = false;
      // });


    },


    methods: {
      getInstance(id) {
        return axios.get('/api/studies/' + id + '/instances')
          .then(
            (data) => {
              return this.instances.push(data.data)
            }
          )
          .catch(
            (er) =>console.log(er)
          )
      },
      goToInstance(instanceId, instanceImage) {

        this.$router.push({name: 'inst-preview',
          params: {
           instanceId: instanceId, imageData: instanceImage
          }
        });
        this.$store.commit('setCurrentImage', instanceImage)
        // this.$router.push({
        //   name: 'studieView',
        //   params: {
        //     studieId: studieId
        //   }
        // })
      },
      getImage(id) {
        axios.get('/api/instances/' + id + '/preview', { responseType: 'arraybuffer'})
          .then(
            (response => {
                console.log( new Buffer.from(response.data, 'binary').toString('base64'))
              }
            )


          )
          .catch(
            (er) => console.error(er)
          )
      },

      encodeImage(arrayBuffer) {
        let u8 = new Uint8Array(arrayBuffer)
        let b64encoded = btoa([].reduce.call(new Uint8Array(arrayBuffer),function(p,c){return p+String.fromCharCode(c)},''))
        let mimetype="image/png"
        return "data:"+mimetype+";base64,"+b64encoded
      },

      getSeries(seriesId) {

        axios.get('/api/series/' + seriesId)
          .then(
            (data) => this.series = data.data
          )
          .catch()

      },
      normalizeName(value) {
        return (value.replace('^', ' ').replace('^^^','')).split(" ")[1] + ' ' + (value.replace('^', ' ').replace('^^^','')).split(" ")[0]

      },
      normalizeDate(value) {
        let year = value.slice(0,4);

        let month = value.slice(4,-2);
        let day = value.slice(6);

        return day + '.' + month + '.' + year
      },
      normalizeTime(value) {
        let hours = value.slice(0, 2);
        let minutes = value.slice(2, -2);
        let seconds = value.slice(4)

        return hours + ':' + minutes + ':' + seconds;
      }
    },
  }
</script>

<style scoped>

</style>
