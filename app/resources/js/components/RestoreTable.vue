<template>
    <div>
        <v-snackbar
            v-model="alert.show"
            :timeout="alert.timeout"
            top
            :color="alert.color"
            >{{ alert.text }}</v-snackbar
        >
        <v-container justify="center">
            <v-breadcrumbs :items="navlist"></v-breadcrumbs>
        </v-container>
        <v-row justify="center">
            <v-col cols="12" md="12" lg="9">
                <v-card>
                    <v-card-title>
                        Deleted Inspections
                        <v-spacer></v-spacer>
                        <v-text-field
                            v-model="search"
                            append-icon="mdi-account-search"
                            label="Search"
                            single-line
                            hide-details
                        ></v-text-field>
                    </v-card-title>
                    <v-data-table
                        :headers="headers"
                        :items="reports"
                        :search="search"
                        :loading="loading"
                        loading-text="Loading... Please wait"
                    >
                        <template v-slot:item.status="{ item }">
                            <v-chip :color="getColor(item.status)" dark>{{
                                item.status
                            }}</v-chip>
                        </template>
                        <template v-slot:item.action="{ item }">
                            <v-icon @click="undeleteItem(item)"
                                >mdi-file-restore</v-icon
                            >
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script>
export default {
    data() {
        return {
            alert: {
                show: false,
                text: " ",
                timeout: 3000,
                color: "black"
            },
            navlist: [
                {
                    text: "Home",
                    disabled: false,
                    href: "/dashboard"
                },
                {
                    text: "Restore Center",
                    disabled: true,
                    href: ""
                }
            ],
            search: "",
            loading: false,
            AuthStr: localStorage.getItem("api"),
            dialog: false,
            users: null,
            headers: [
                {
                    text: "Lab",
                    align: "left",
                    sortable: true,
                    value: "lab",
                    width: "100px"
                },
                { text: "Status", value: "status", width: "100px" },

                { text: "Report", value: "title", width: "150px" },
                { text: "Assigned To", value: "user_name", width: "150px" },
                { text: "Due Date", value: "due_date", width: "100px" },
                {
                    text: "Deleted At",
                    align: "left",
                    sortable: true,
                    value: "deleted_at",
                    width: "100px"
                },
                {
                    text: "Restore",
                    value: "action",
                    sortable: false,
                    width: "50px"
                }
            ],
            defaultItem: {},
            temp_reports: [],
            reports: []
        };
    },
    watch: {
        dialog(val) {
            val || this.close();
        }
    },
    computed: {},
    created() {
        this.initialize();
    },
    methods: {
        getNamebyId(t_id) {
            let n = this.users.find(x => x.id == t_id);
            return n.first_name + " " + n.last_name;
        },
        viewLink(i) {
            return "/assignment/" + i.id;
        },
        getColor(status) {
            if (status == "Pending") return "blue";
            else if (status == "Submitted") return "green";
            else if (status == "Overdue") return "red";
            else return "grey";
        },
        setSnack(on, txt, col, time) {
            this.alert.show = on;
            this.alert.text = txt;
            this.alert.color = col;
        },
        initialize() {
            (this.loading = true),
                axios
                    .get("/api/v1/restore_reports", {
                        headers: { Authorization: "Bearer " + this.AuthStr }
                    })
                    .then(
                        response => {
                            this.temp_reports = response.data.data;
                            this.loading = false;
                            return axios.get("/api/users", {
                                headers: {
                                    Authorization: "Bearer " + this.AuthStr
                                }
                            });
                        },
                        error => {
                            this.loading = false;
                        }
                    )
                    .then(
                        response => {
                            this.users = response.data.data;
                            this.temp_reports.map(rpt => {
                                rpt.user_name = this.getNamebyId(
                                    rpt.assigned_to
                                );
                            });
                            this.reports = this.temp_reports;
                            this.loading = false;
                        },
                        error => {
                            this.loading = false;
                            this.setSnack(
                                true,
                                "Failed to retrieve deleted assignments.",
                                "error"
                            );
                        }
                    );
        },

        undeleteItem(item) {
            const index = this.reports.indexOf(item);
            this.loading = true;
            if (confirm("Are you sure you want to restore this item?")) {
                axios
                    .post("/api/v1/restore_report/" + item.id, null, {
                        headers: {
                            Authorization: "Bearer " + this.AuthStr,
                            "Content-Type": "application/json"
                        }
                    })
                    .then(
                        response => {
                            this.setSnack(
                                true,
                                "Inspection restored successfully",
                                "success"
                            );
                            this.reports.splice(index, 1);
                            this.loading = false;
                        },
                        error => {
                            this.setSnack(
                                true,
                                "Failed to restore assignment",
                                "error"
                            );
                            this.loading = false;
                        }
                    );
            }
        }
    }
};
</script>
