<template>
  <div>
    <div class="search">
      <ul>
        <!-- <li>
          <button @click="searchInput('name')">name</button>
          <input v-if="inputType == 'name'" v-model="searchKey" type="text" />
        </li> -->
        <!-- <li>
          <button @click="searchInput('tech')">Technicien</button>
          <select v-if="inputType == 'tech'" v-model="searchKey" type="text">
            <option value="">Technicien</option>
            <option v-for="affaire in affaires" :key="affaire.affairid">
              {{ affaire.tech_name }}
            </option>
          </select>
        </li> -->
        <li>
          <button @click="searchInput('affaire')">affaire</button>
          <select v-if="inputType == 'affaire'" v-model="affaireSelected">
            <option value="">Choisissez</option>
            <option v-for="affaire in affaires" :key="affaire.affairid"
              >{{ affaire.name }}
            </option>
          </select>
        </li>
      </ul>
    </div>

    <form @submit.prevent="addAffaire">
      <div v-for="affaire in search" :key="affaire.affairid">
        <div class="entete">
          <input
            v-model="affaire.name"
            class="titre_affaire"
            type="text"
            placeholder="Nom de l'affaire"
          />

          <input class="button" type="submit" value="Ma liste" />
          <input class="button" type="submit" value="New" />
        </div>
        <div class="dates">
          <label for="prepa">Prépa</label>
          <input
            v-model="affaire.prep_date"
            style="width:140px"
            type="date"
            name="retour"
          />
          <label for="r_am">matin</label>
          <input v-model="affaire.prep_time" type="checkbox" name="r_am" />
          <label for="r_pm">aprèsMidi</label>
          <input type="checkbox" name="r_pm" />
        </div>
        <div>
          <div class="dates">
            <label for="sortie">Sortie</label>
            <input
              v-model="affaire.receipt_date"
              style="width:140px"
              type="date"
              name="sortie"
            />
            <label for="s_matin">matin</label>
            <input
              v-model="affaire.receipt_time"
              :true-value="1"
              :false-value="0"
              type="checkbox"
              name="s_matin"
            />
            <label for="s_apm">aprèsMidi</label>
            <input
              v-model="affaire.receipt_time"
              :true-value="0"
              :false-value="1"
              type="checkbox"
              name="s_apm"
            />
          </div>
          <div class="dates">
            <label for="retour">Retour</label>
            <input
              v-model="affaire.return_date"
              style="width:140px"
              type="date"
              name="retour"
            />
            <label for="r_am">matin</label>
            <input v-model="affaire.return_time" type="checkbox" name="r_am" />
            <label for="r_pm">aprèsMidi</label>
            <input type="checkbox" name="r_pm" />
          </div>

          <div class="cont_2">
            <div class="tech">
              <input
                v-model="affaire.tech_name"
                type="text"
                placeholder="Nom du technicien"
              />

              <label for="face">face</label>
              <input
                v-model="affaire.front"
                :true-value="1"
                :false-value="0"
                type="checkbox"
                name="face"
              />
              <label for="mon"> mon</label>
              <input
                v-model="affaire.monitor"
                :true-value="1"
                :false-value="0"
                type="checkbox"
                name="mon"
              />
              <label for="scene"> scène</label>
              <input
                v-model="affaire.stage"
                :true-value="1"
                :false-value="0"
                type="checkbox"
                name="scene"
              />
              <input class="button" type="submit" value="Résumé" />
              <input class="button" type="submit" value="note" />
            </div>
            <div style="display:flex">
              <div>
                <input @click="submit" class="button" type="submit" />

                <label for="end">Terminé </label>
                <input type="checkbox" name="end" id="end" />
              </div>

              <div>
                <label for="update">Update </label>
                <input
                  v-model="affaire.timestamp"
                  style="width:150px"
                  type="timestamp"
                  name="update"
                />
              </div>
            </div>
          </div>
        </div>
        <div class="poste">
          <input class="button" type="submit" value="HP" />
          <input class="button" type="submit" value="Module" />
          <input class="button" type="submit" value="Elec" />
          <input class="button" type="submit" value="Spécial" />
          <input class="button" type="submit" value="Micros" />
        </div>

        <div>
          <input style="display:none" type="checkbox" name="ok" />
          <h5 class="list_name"></h5>
          <input style="border:0px" type="text" placeholder="nb" />
          <input style="border:0px" type="number" placeholder="secu" />
          <input style="border:0px" type="number" placeholder="dispo" />
        </div>
        <div class="list" v-for="cable in cables" :key="cable.cableid">
          <input type="checkbox" name="ok" />
          <h5 class="list_name">{{ cable.name }}</h5>
          <input v-model="order.count" type="int" name="nb" />
          <input type="number" name="secu" />
          <input type="number" name="dispo" />
        </div>

        <div>
          <p>affairid: {{ affaire.affairid }}</p>
        </div>
        <div>
          <span>{{ cable.name }} </span>
        </div>
      </div>
    </form>

    <br />
  </div>
</template>

<script>
import { Api } from "../js/api.js";
var url = "https://cinod.fr/cables/api.php";

var api = new Api(url);

export default {
  data() {
    return {
      cables: [],
      cable: [],
      affaires: [],
      affairid: "",
      front: "",
      master_note: null,
      monitor: "",
      name: "",
      prep_date: Date,
      prep_time: null,
      receipt_date: Date,
      receipt_time: null,
      ref: "",
      return_date: "",
      return_time: null,
      stage: "1",
      tech_id: "3",
      tech_name: "",
      tech_note: null,
      timestamp: "",

      orders: [],
      cableid: "",
      count: "",
      done: "",
      orderid: "",

      searchKey: "",
      searchTechKey: "",
      affaireSelected: "",
      inputType: ""
    };
  },

  computed: {
    search() {
      //eslint-disable-next-line prettier/prettier
      return this.affaires.filter(affaire => {
        return affaire.name.includes(this.affaireSelected);
      });
    },
    orderfilter() {
      return this.orders.filter(order => {
        return order.orderid == this.affaire.affairid;
      });
    }
  },
  methods: {
    searchInput(arg) {
      this.inputType = arg;
    }
  },

  mounted() {
    // const searchby = {
    //   tech_id: 3
    //   //name: ""
    // };
    api
      .call("affair_get")
      .then(response => {
        this.affaires = response;
        console.log("affair_get:", response);
      })
      .catch(function(response) {
        console.log("affair_get:", response);
      });

    api
      .call("cable_get")
      .then(response => {
        console.log("cable_get:", response);
        this.cables = response;
      })
      .catch(response => {
        console.log("cable_get:", response);
      });

    api
      .call("order_get")
      .then(response => {
        console.log("order_get:", response);
        this.orders = response;
      })
      .catch(response => {
        console.log("err_order_get:", response);
      });
  }
};
</script>

<style scoped>
input {
  padding: 5px;
}
.entete {
  display: flex;
  justify-content: space-around;
}
.titre_affaire {
  width: 200px;
  height: 32px;
  left: 34px;
  top: 63px;

  background: linear-gradient(180deg, #ffffff 0%, rgba(255, 255, 255, 0) 100%);
  border: 1px solid #000000;
  box-sizing: border-box;
  box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
}
.button {
  margin: 10px;
  padding: 5px;
  min-width: 50px;
  background: #4dcc59;
  border: 1px solid #000000;
  box-sizing: border-box;
  box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
  border-radius: 4px;
}
.dates {
  margin: 10px;
  display: flex;
  justify-content: space-around;
}
.cont_2 {
  display: flex;
}
.cont_2 input {
  margin: 5px;
}
.tech {
  width: 200px;
}
.poste {
  margin: 10px 0px 20px 0px;
}
.list {
  margin: 5px 25px;
  display: flex;
  text-align: left;
}

.list input {
  margin: 0px 10px;
  width: 30px;
}
.list_name {
  width: 220px;
}
</style>
