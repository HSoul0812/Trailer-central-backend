<template>
    <div>
        <div class="mb-6">
            <heading class="mb-6">Bulk Showroom Years</heading>

            <card class="mb-6">
                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="manufacturer-year" class="inline-block text-80 pt-2 leading-tight">
                            Manufacturer
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="manufacturer-year"
                                name="manufacturer-year"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkYear.manufacturer">
                            <option v-for="manufacturer in manufacturers">{{ manufacturer.name }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Select a Manufacturer to Bulk Change years
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="from-year" class="inline-block text-80 pt-2 leading-tight">
                            Current Year
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="from-year"
                                name="from-year"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkYear.from">
                            <option v-for="year in years">{{ year }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Select the current year from the Manufacturer to Bulk Change
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-40 remove-bottom-border">
                    <div class="w-1/5 px-8 py-6">
                        <label for="to-year" class="inline-block text-80 pt-2 leading-tight">
                            Switch to Year
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="to-year"
                                name="to-year"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkYear.to">
                            <option v-for="year in years">{{ year }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Select the target year for the Manufacturer to Bulk Change
                        </div>
                    </div>
                </div>
            </card>

            <div class="flex items-center">
                <a tabindex="0" class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">
                    Cancel
                </a>

                <button type="button" class="btn btn-default btn-primary inline-flex items-center relative mr-3" dusk="create-and-add-another-button"
                        v-on:click="postBulkYearForm">
                        <span class="">
                          Bulk Change Years
                        </span>
                    <!---->
                </button>
            </div>
        </div>

        <div class="mb-6">
            <heading class="mb-6">Bulk Showroom Visibility</heading>

            <card class="mb-6">
                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="manufacturer-visibility" class="inline-block text-80 pt-2 leading-tight">
                            Manufacturer
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="manufacturer-visibility"
                                name="mmanufacturer-visibility"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkVisibility.manufacturer">
                            <option v-for="manufacturer in manufacturers">{{ manufacturer.name }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Select a Manufacturer to Bulk Change visibility
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="year" class="inline-block text-80 pt-2 leading-tight">
                            Year
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="year"
                                name="year"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkVisibility.year">
                            <option v-for="year in years">{{ year }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Select the current year from the Manufacturer to Bulk Change visibility
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-40 remove-bottom-border">
                    <div class="w-1/5 px-8 py-6">
                        <label for="visibility" class="inline-block text-80 pt-2 leading-tight">
                            Visibility
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <input v-model="formBulkVisibility.visibility" type="checkbox" name="visibility" id="visibility" class="checkbox" :value="false">
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            Mark visibility for the Manufacturer year to Bulk Change
                        </div>
                    </div>
                </div>
            </card>

            <div class="flex items-center">
                <a tabindex="0" class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">
                    Cancel
                </a>

                <button type="submit" class="btn btn-default btn-primary inline-flex items-center relative" dusk="create-button"
                        v-on:click="postBulkVisibilityForm">
                        <span class="">
                          Bulk Change Visibility
                        </span>
                    <!---->
                </button>
            </div>
        </div>

        <div class="mb-6">
            <heading class="mb-6">Bulk Inventory Manufacturer</heading>

            <card class="mb-6">
                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="manufacturer-inv-change" class="inline-block text-80 pt-2 leading-tight">
                            From Manufacturer
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="manufacturer-inv-change"
                                name="manufacturer-inv-change"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkInvMfg.from_manufacturer">
                            <option v-for="manufacturer in manufacturers">{{ manufacturer.name }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            This is the Manufacturer that is going to be changed
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-40">
                    <div class="w-1/5 px-8 py-6">
                        <label for="manufacturer-inv-change" class="inline-block text-80 pt-2 leading-tight">
                            To Manufacturer
                        </label>
                    </div>
                    <div class="py-6 px-8 w-1/2">
                        <select id="manufacturer-inv-change"
                                name="manufacturer-inv-change"
                                class="w-full form-control form-input form-input-bordered"
                                v-model="formBulkInvMfg.to_manufacturer">
                            <option v-for="manufacturer in manufacturers">{{ manufacturer.name }}</option>
                        </select>
                        <!---->
                        <!---->
                        <div class="help-text help-text mt-2">
                            This is the Manufacturer that will be the new one per inventory
                        </div>
                    </div>
                </div>
            </card>

            <div class="flex items-center">
                <a tabindex="0" class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6">
                    Cancel
                </a>

                <button type="submit" class="btn btn-default btn-primary inline-flex items-center relative" dusk="create-button"
                        v-on:click="postBulkInvMfgForm">
                        <span class="">
                          Bulk Change Inv Manufacturer
                        </span>
                    <!---->
                </button>
            </div>
        </div>

        <div v-if="debug">
            <heading class="mb-6">Debug</heading>

            <card>
                <div class="p-4">
                    <div class="mb-2">
                        <span>Bulk Showroom Year</span>
                    </div>
                    <pre style="background-color: black; color: white;">{{ formBulkYear }}</pre>
                </div>

                <div class="p-4">
                    <div class="mb-2">
                        <span>Bulk Showroom Visibility</span>
                    </div>
                    <pre style="background-color: black; color: white;">{{ formBulkVisibility }}</pre>
                </div>

                <div class="p-4">
                    <div class="mb-2">
                        <span>Bulk Inventory Manufacturer</span>
                    </div>
                    <pre style="background-color: black; color: white;">{{ formBulkInvMfg }}</pre>
                </div>
            </card>
        </div>

    </div>
</template>

<script>
export default {
    data() {
        return {
            debug: false,
            response: '',

            formBulkYear: {
                manufacturer: '',
                from: '',
                to: '',
            },

            formBulkVisibility: {
                manufacturer: '',
                year: '',
                visibility: false,
            },

            formBulkInvMfg: {
                from_manufacturer: '',
                to_manufacturer: ''
            },

            manufacturers: [],
            years: []
        }
    },

    computed: {
    },

    methods: {
        async getManufacturers() {
            await Nova.request().get('/api/manufacturers')
                .then((response) => {
                    this.manufacturers = response.data.data;
                });
        },
        loadYears(startYear, endYear) {
            let now = moment(startYear).clone();

            while (now.isSameOrBefore(moment(endYear))) {
                this.years.push(now.format('YYYY'));
                now.add(1, 'years');
            }
        },

        async postBulkYearForm() {
            await axios.post('/api/manufacturers/bulk_year', this.formBulkYear)
                .then((response) => {
                    this.$toasted.show(response.data.message, { type: response.data.status })
                });
        },
        async postBulkVisibilityForm() {
            await axios.post('/api/manufacturers/bulk_visibility', this.formBulkVisibility)
                .then((response) => {
                    this.$toasted.show(response.data.message, { type: response.data.status })
                });
        },
        async postBulkInvMfgForm() {
            await axios.post('/api/inventories/bulk_manufacturer', this.formBulkInvMfg)
                .then((response) => {
                    this.$toasted.show(response.data.message, { type: response.data.status })
                });
        },
    },

    beforeMount() {
        this.loadYears("2019-01-01", "2025-01-01");
        this.getManufacturers();
    }
}
</script>

<style>
/* Scoped Styles */
</style>
