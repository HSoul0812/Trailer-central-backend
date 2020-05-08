<template>
    <div>
        <heading class="mb-6">Edit Mapping</heading>

        <div class="py-6 px-8 w-full">
            <div class="relative rounded-lg rounded-b-lg bg-30 bg-clip border border-60 mr-11">
                <div class="bg-30 rounded-t-lg flex border-b border-50">
                    <div class="bg-clip w-48 uppercase font-bold text-xs text-80 tracking-wide px-3 py-3">
                        Field
                    </div>
                    <div
                        class="bg-clip flex-grow uppercase font-bold text-xs text-80 tracking-wide px-3 py-3 border-l border-50">
                        Sample
                    </div>
                    <div
                        class="bg-clip flex-grow uppercase font-bold text-xs text-80 tracking-wide px-3 py-3 border-l border-50">
                        Target
                    </div>
                </div>
                <div class="bg-white overflow-hidden key-value-items">
                    <div class="flex items-center key-value-item">
                        <div class="flex flex-grow border-b border-50 key-value-fields">
                            <div class="w-48 cursor-text"><textarea dusk="key-value-key-0" type="text"
                                                                    class="font-mono text-sm resize-none block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                    style="background-clip: border-box; overflow: hidden; overflow-wrap: break-word; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><textarea dusk="key-value-value-0" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><select dusk="key-value-value-0" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">
                                <option>Name</option>
                                <option>Type</option>
                                <option>Description</option>
                                <option>Model</option>
                                <option>Make</option>
                                <option>Year</option>
                            </select>
                            </div>
                        </div>
                        <div class="flex justify-center h-11 w-11 absolute" style="right: -50px;">
                            <button type="button" tabindex="-1" title="Delete"
                                    class="flex appearance-none cursor-pointer text-70 hover:text-primary active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                     aria-labelledby="delete" role="presentation" class="fill-current">
                                    <path fill-rule="nonzero"
                                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center key-value-item">
                        <div class="flex flex-grow border-b border-50 key-value-fields">
                            <div class="w-48 cursor-text"><textarea dusk="key-value-key-1" type="text"
                                                                    class="font-mono text-sm resize-none block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                    style="background-clip: border-box; overflow: hidden; overflow-wrap: break-word; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><textarea dusk="key-value-value-1" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><select dusk="key-value-value-0" type="text"
                                                                              class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                              style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">
                                <option>Name</option>
                                <option>Type</option>
                                <option>Description</option>
                                <option>Model</option>
                                <option>Make</option>
                                <option>Year</option>
                            </select>
                            </div>
                        </div>
                        <div class="flex justify-center h-11 w-11 absolute" style="right: -50px;">
                            <button type="button" tabindex="-1" title="Delete"
                                    class="flex appearance-none cursor-pointer text-70 hover:text-primary active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                     aria-labelledby="delete" role="presentation" class="fill-current">
                                    <path fill-rule="nonzero"
                                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center key-value-item">
                        <div class="flex flex-grow border-b border-50 key-value-fields">
                            <div class="w-48 cursor-text"><textarea dusk="key-value-key-2" type="text"
                                                                    class="font-mono text-sm resize-none block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                    style="background-clip: border-box; overflow: hidden; overflow-wrap: break-word; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><textarea dusk="key-value-value-2" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><select dusk="key-value-value-0" type="text"
                                                                              class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                              style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">
                                <option>Name</option>
                                <option>Type</option>
                                <option>Description</option>
                                <option>Model</option>
                                <option>Make</option>
                                <option>Year</option>
                            </select>
                            </div>
                        </div>
                        <div class="flex justify-center h-11 w-11 absolute" style="right: -50px;">
                            <button type="button" tabindex="-1" title="Delete"
                                    class="flex appearance-none cursor-pointer text-70 hover:text-primary active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                     aria-labelledby="delete" role="presentation" class="fill-current">
                                    <path fill-rule="nonzero"
                                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center key-value-item">
                        <div class="flex flex-grow border-b border-50 key-value-fields">
                            <div class="w-48 cursor-text"><textarea dusk="key-value-key-3" type="text"
                                                                    class="font-mono text-sm resize-none block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                    style="background-clip: border-box; overflow: hidden; overflow-wrap: break-word; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><textarea dusk="key-value-value-3" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><select dusk="key-value-value-0" type="text"
                                                                              class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                              style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">
                                <option>Name</option>
                                <option>Type</option>
                                <option>Description</option>
                                <option>Model</option>
                                <option>Make</option>
                                <option>Year</option>
                            </select>
                            </div>
                        </div>
                        <div class="flex justify-center h-11 w-11 absolute" style="right: -50px;">
                            <button type="button" tabindex="-1" title="Delete"
                                    class="flex appearance-none cursor-pointer text-70 hover:text-primary active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                     aria-labelledby="delete" role="presentation" class="fill-current">
                                    <path fill-rule="nonzero"
                                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center key-value-item">
                        <div class="flex flex-grow border-b border-50 key-value-fields">
                            <div class="w-48 cursor-text"><textarea dusk="key-value-key-4" type="text"
                                                                    class="font-mono text-sm resize-none block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                    style="background-clip: border-box; overflow: hidden; overflow-wrap: break-word; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><textarea dusk="key-value-value-4" type="text"
                                                                                class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                                style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;"></textarea>
                            </div>
                            <div class="flex-grow border-l border-50"><select dusk="key-value-value-0" type="text"
                                                                              class="font-mono text-sm hover:bg-20 focus:bg-white block min-h-input w-full form-control form-input form-input-row py-4 text-90 hover:bg-20 focus:bg-white"
                                                                              style="overflow: hidden; overflow-wrap: break-word; resize: none; height: 48px;">
                                <option>Name</option>
                                <option>Type</option>
                                <option>Description</option>
                                <option>Model</option>
                                <option>Make</option>
                                <option>Year</option>
                            </select>
                            </div>
                        </div>
                        <div class="flex justify-center h-11 w-11 absolute" style="right: -50px;">
                            <button type="button" tabindex="-1" title="Delete"
                                    class="flex appearance-none cursor-pointer text-70 hover:text-primary active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                     aria-labelledby="delete" role="presentation" class="fill-current">
                                    <path fill-rule="nonzero"
                                          d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mr-11">
                <button type="button"
                        class="btn btn-link dim cursor-pointer rounded-lg mx-auto text-primary mt-3 px-3 rounded-b-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         aria-labelledby="add" role="presentation" class="fill-current">
                        <path
                            d="M17 11a1 1 0 0 1 0 2h-4v4a1 1 0 0 1-2 0v-4H7a1 1 0 0 1 0-2h4V7a1 1 0 0 1 2 0v4h4z"></path>
                    </svg>
                    <span class="ml-1">Add row</span></button>
            </div> <!---->
            <div class="help-text help-text mt-2">

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            //
        },
    }
</script>

<style>
    /* Scoped Styles */
</style>
