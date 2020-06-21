<template>
    <div>
        <portal to="modals">
            <modal
                    v-if="modalOpen"
                    class="modal"
                    tabindex="-1"
                    role="dialog"
            >
                <div class="bg-white rounded-lg shadow-lg overflow-hidden w-action">
                    <div>
                        <h2 class="pt-8 px-8 text-90 font-normal text-xl">{{runningCommand.label}}</h2>
                        <p class="text-80 px-8 my-8" v-html="runningCommand.help"></p>
                    </div>

                    <div class="flex border-b border-40 p-2 flex-col" v-for="variable,index in runningCommand.variables">
                        <label class="inline-block text-80 pt-2 leading-tight w-full capitalize">{{ variable.label }}</label>
                        <select v-if="variable.field === 'select'" class="form-control form-select" v-model="variable.value">
                            <option value="" disabled="disabled">{{ variable.placeholder }}</option>
                            <option :value="key" v-for="label,key in variable.options">{{ label }}</option>
                        </select>
                        <input
                                v-else
                                :type="variable.field"
                                class="w-full form-control form-input form-input-bordered w-full"
                                v-model="variable.value"
                                :placeholder="variable.placeholder"
                        />
                    </div>

                    <div class="flex border-b border-40 p-2" v-for="flag,index in runningCommand.flags">
                        <input type="checkbox" :id="'flag-'+index" class="checkbox m-2" v-model="runningCommand.flags[index].selected" />
                        <label class="m-2" :for="'flag-'+index">{{ flag.label }}</label>
                    </div>

                    <div class="bg-30 px-6 py-3 flex">
                        <div class="flex items-center ml-auto">
                            <button type="button" @click.prevent="closeModal" class="btn text-80 font-normal h-9 px-3 mr-3 btn-link">Cancel</button>

                            <button @click="runCommand()"
                                    type="submit"
                                    class="btn btn-default"
                                    v-bind:class="runningCommand.type ? ('btn-'+ runningCommand.type) : 'btn-primary'"
                            >
                                <span v-if="!running">Run Command</span>
                                <svg v-if="running" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="mx-auto block" style="width: 30px;">
                                    <circle cx="15" cy="15" r="10.9958">
                                        <animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite"></animate>
                                        <animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate>
                                    </circle>
                                    <circle cx="60" cy="15" r="13.0042" fill-opacity="0.3">
                                        <animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;15;9" calcMode="linear" repeatCount="indefinite"></animate>
                                        <animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite"></animate>
                                    </circle>
                                    <circle cx="105" cy="15" r="10.9958">
                                        <animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite"></animate>
                                        <animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate>
                                    </circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </modal>
        </portal>

        <heading class="mb-6">{{ heading }}</heading>

        <card v-if="help" v-html="help" class="p-3"></card>

        <div class="flex mt-6 mb-6" v-if="! Array.isArray(customCommands)">
            <select class="form-control form-select rounded-r-none" v-model="customCommand.command_type">
                <option :value="key" v-for="label,key in customCommands">{{ label }}</option>
            </select>
            <input type="text" v-model="customCommand.command" placeholder="Enter a Command..." class="w-full form-control form-input form-input-bordered mr-2 rounded-l-none">
            <button type="button" class="items-right btn btn-default btn-primary" @click="runCustomCommand">Run</button>
        </div>

        <card class="flex flex-col items-center" style="min-height: 300px">
            <h2 class="text-black text-3xl font-light mt-4">
                Available commands
            </h2>

            <div class="flex bg-grey-lighter">
                <div v-for="group in groups" class="flex-1 text-grey-darker text-center bg-grey-light px-4 py-2 m-2">
                    <h4 class="text-black text-2xl text-60 font-light mb-6 mt-4">
                        {{group ? group : 'Unnamed group'}}
                    </h4>
                    <button type="button"
                            v-for="(command, index) in commands"
                            v-if="command.group===group"
                            @click="openModal(command)"
                            class="items-left btn btn-default mb-2"
                            style="width: 100%;"
                            v-bind:class="command.type ? ('btn-'+ command.type) : 'btn-primary'"
                    >
                        {{command.label}}
                    </button>
                </div>
            </div>


        </card>

        <div class="flex justify-between mt-6 mb-6">
            <heading class="">History</heading>
            <button type="button" class="items-right btn btn-default btn-danger" @click="clearHistory">Clear History</button>
        </div>

        <card class="mb-6 max-w-full overflow-x-auto">
            <table class="table w-full">
                <thead>
                <tr>
                    <th scope="col">Command</th>
                    <th scope="col">Type</th>
                    <th scope="col">Ran By</th>
                    <th scope="col">Status</th>
                    <th scope="col">Result</th>
                    <th scope="col">Duration</th>
                    <th scope="col" class="table-fit">Happened</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="value in history">
                    <td>{{value.run}}</td>
                    <td>{{value.type}}</td>
                    <td>{{value.ran_by}}</td>
                    <td><span class="badge" v-bind:class="'badge-'+value.status">{{value.status}}</span></td>
                    <td><pre v-html="value.result"></pre></td>
                    <td>{{value.duration}} sec.</td>
                    <td class="table-fit">{{value.time}}</td>
                </tr>
                </tbody>
            </table>
        </card>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                modalOpen: false,
                running: false,
                groups: [],
                commands: {},
                customCommand : {
                    label : 'Custom Command',
                    type : 'primary',
                    help : 'are you sure you want to run this command?',
                    command_type :'artisan',
                    command : '',
                    variables : [],
                    flags : []
                },
                history: {},
                runningCommand : {},
                help : '',
                heading : '',
                customCommands : {},
            }
        },
        created() {
            this.getData();
        },
        methods: {

            clearHistory(){
                this.openModal( {
                    label : 'Clear Command Run History',
                    type : 'primary',
                    help : 'are you sure you want to clear the command run history?',
                    command_type :'artisan',
                    command : 'cache:forget nova-advanced-command-runner-history',
                    variables : [],
                    flags : []
                } );
            },

            runCustomCommand(){
                if(!this.customCommand.command){
                    this.$toasted.show('Please enter a command', {type: 'error'});
                    return;
                }
                this.openModal( this.customCommand );
            },
            getData() {
                Nova.request().get('/nova-vendor/binarybuilds/nova-advanced-command-runner')
                    .then(response => {
                        this.groups = [];

                        response.data.commands.forEach(command => {
                            let group = command.group;
                            if (this.groups.indexOf(group) < 0) {
                                this.groups.push(group);
                            }
                        });


                        this.commands = response.data.commands;
                        this.history = response.data.history;
                        this.help = response.data.help;
                        this.heading = response.data.heading;
                        this.customCommands = response.data.custom_commands;

                        if(this.customCommands){
                            this.customCommand.command_type = Object.keys(this.customCommands)[0];
                        }
                    });
            },
            runCommand() {

                let readyToSubmit = true;

                if( this.runningCommand.variables ){
                    Object.keys(this.runningCommand.variables).forEach( variable => {
                        if( ! this.runningCommand.variables[variable].value ){
                            readyToSubmit = false;
                            this.$toasted.show(this.runningCommand.variables[variable].label +' is required', {type: 'error'});
                        }
                    });
                }

                if(! readyToSubmit ){
                    return;
                }

                this.running = true;
                Nova.request().post('/nova-vendor/binarybuilds/nova-advanced-command-runner/run', { command : this.runningCommand } )
                    .then(response => {
                        this.$toasted.show(response.data.result, {type: response.data.status ? 'success' : 'error' });
                        this.running = false;
                        this.history = response.data.history;
                        this.closeModal();
                    }).catch(error => {
                    this.running = false;
                });
            },
            openModal(command) {
                this.runningCommand = command;
                this.modalOpen = true;
            },
            closeModal() {
                this.modalOpen = false;
                this.runningCommand = {};
            },
        },
    }
</script>

<style>
    .history-table {
        text-align: left;
    }

    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        color: #fff;
        background-color: #5a6268;
        border-color: #545b62;
    }

    .btn-secondary:focus, .btn-secondary.focus {
        box-shadow: 0 0 0 0.2rem rgba(130, 138, 145, 0.5);
    }

    .btn-secondary.disabled, .btn-secondary:disabled {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:not(:disabled):not(.disabled):active, .btn-secondary:not(:disabled):not(.disabled).active,
    .show > .btn-secondary.dropdown-toggle {
        color: #fff;
        background-color: #545b62;
        border-color: #4e555b;
    }

    .btn-secondary:not(:disabled):not(.disabled):active:focus, .btn-secondary:not(:disabled):not(.disabled).active:focus,
    .show > .btn-secondary.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(130, 138, 145, 0.5);
    }

    .btn-success {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        color: #fff;
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-success:focus, .btn-success.focus {
        box-shadow: 0 0 0 0.2rem rgba(72, 180, 97, 0.5);
    }

    .btn-success.disabled, .btn-success:disabled {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:not(:disabled):not(.disabled):active, .btn-success:not(:disabled):not(.disabled).active,
    .show > .btn-success.dropdown-toggle {
        color: #fff;
        background-color: #1e7e34;
        border-color: #1c7430;
    }

    .btn-success:not(:disabled):not(.disabled):active:focus, .btn-success:not(:disabled):not(.disabled).active:focus,
    .show > .btn-success.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(72, 180, 97, 0.5);
    }

    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        color: #fff;
        background-color: #138496;
        border-color: #117a8b;
    }

    .btn-info:focus, .btn-info.focus {
        box-shadow: 0 0 0 0.2rem rgba(58, 176, 195, 0.5);
    }

    .btn-info.disabled, .btn-info:disabled {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:not(:disabled):not(.disabled):active, .btn-info:not(:disabled):not(.disabled).active,
    .show > .btn-info.dropdown-toggle {
        color: #fff;
        background-color: #117a8b;
        border-color: #10707f;
    }

    .btn-info:not(:disabled):not(.disabled):active:focus, .btn-info:not(:disabled):not(.disabled).active:focus,
    .show > .btn-info.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(58, 176, 195, 0.5);
    }

    .btn-warning {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-warning:hover {
        color: #212529;
        background-color: #e0a800;
        border-color: #d39e00;
    }

    .btn-warning:focus, .btn-warning.focus {
        box-shadow: 0 0 0 0.2rem rgba(222, 170, 12, 0.5);
    }

    .btn-warning.disabled, .btn-warning:disabled {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-warning:not(:disabled):not(.disabled):active, .btn-warning:not(:disabled):not(.disabled).active,
    .show > .btn-warning.dropdown-toggle {
        color: #212529;
        background-color: #d39e00;
        border-color: #c69500;
    }

    .btn-warning:not(:disabled):not(.disabled):active:focus, .btn-warning:not(:disabled):not(.disabled).active:focus,
    .show > .btn-warning.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(222, 170, 12, 0.5);
    }

    .btn-light {
        color: #212529;
        background-color: #f8f9fa;
        border-color: #f8f9fa;
    }

    .btn-light:hover {
        color: #212529;
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }

    .btn-light:focus, .btn-light.focus {
        box-shadow: 0 0 0 0.2rem rgba(216, 217, 219, 0.5);
    }

    .btn-light.disabled, .btn-light:disabled {
        color: #212529;
        background-color: #f8f9fa;
        border-color: #f8f9fa;
    }

    .btn-light:not(:disabled):not(.disabled):active, .btn-light:not(:disabled):not(.disabled).active,
    .show > .btn-light.dropdown-toggle {
        color: #212529;
        background-color: #dae0e5;
        border-color: #d3d9df;
    }

    .btn-light:not(:disabled):not(.disabled):active:focus, .btn-light:not(:disabled):not(.disabled).active:focus,
    .show > .btn-light.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(216, 217, 219, 0.5);
    }

    .btn-dark {
        color: #fff;
        background-color: #343a40;
        border-color: #343a40;
    }

    .btn-dark:hover {
        color: #fff;
        background-color: #23272b;
        border-color: #1d2124;
    }

    .btn-dark:focus, .btn-dark.focus {
        box-shadow: 0 0 0 0.2rem rgba(82, 88, 93, 0.5);
    }

    .btn-dark.disabled, .btn-dark:disabled {
        color: #fff;
        background-color: #343a40;
        border-color: #343a40;
    }

    .btn-dark:not(:disabled):not(.disabled):active, .btn-dark:not(:disabled):not(.disabled).active,
    .show > .btn-dark.dropdown-toggle {
        color: #fff;
        background-color: #1d2124;
        border-color: #171a1d;
    }

    .btn-dark:not(:disabled):not(.disabled):active:focus, .btn-dark:not(:disabled):not(.disabled).active:focus,
    .show > .btn-dark.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(82, 88, 93, 0.5);
    }


    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .badge-success {
        color: #fff;
        background-color: #28a745;
    }

    .badge-error {
        color: #fff;
        background-color: #dc3545;
    }
</style>
