<?php

namespace BinaryBuilds\NovaAdvancedCommandRunner\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

/**
 * Class CommandsController
 * @package BinaryBuilds\NovaAdvancedCommandRunner\Http\Controllers
 */
class CommandsController
{
    /**
     * @return array
     */
    public function index()
    {
        $data = config('nova-advanced-command-runner');
        $raw_commands = isset($data['commands']) ? $data['commands'] : [];
        $commands = [];
        if(is_array($commands)){
            foreach ($raw_commands as $label => $command ){
                $parsed = [
                    'label' => $label,
                    'command' => $command['run'],
                    'variables' => [],
                    'flags' => [],
                    'type' => isset($command['type']) ? $command['type'] : 'primary',
                    'group' => isset($command['group']) ? $command['group'] : 'Unnamed Group',
                    'help' => isset($command['help']) ? $command['help'] : 'Are you sure you want to run this command?',
                    'command_type' => isset($command['command_type']) && $command['command_type'] === 'bash' ? 'bash' : 'artisan'
                ];

                preg_match_all( '~(?<={).+?(?=})~', $command['run'], $matches );

                if( ! empty($matches[0]) ){
                    foreach ($matches[0] as $variable ){
                        $parsed['variables'][$variable] = [ 'label' => $variable, 'field' => 'text', 'placeholder' => $variable, 'value' => '' ];
                    }
                }

                if(isset($command['flags']) && is_array($command['flags'])){
                    foreach ( $command[ 'flags' ] as $flag => $label ){
                        array_push($parsed['flags'], [
                            'label' => $label,
                            'flag' => $flag,
                            'selected' => false
                        ]);
                    }
                }

                if(isset($command['variables']) && is_array($command['variables'])){
                    foreach ($command['variables'] as $variable){
                        $parsed['variables'][$variable['label']] = [
                            'label' => $variable['label'],
                            'field' => isset($variable['field']) ? $variable['field'] : 'text',
                            'value' => '',
                            'options' => isset($variable['options']) ? $variable['options'] : [],
                            'placeholder' => isset($variable['placeholder']) ? $variable['placeholder'] : $variable['label']
                        ];
                    }
                }

                array_push($commands, $parsed);
            }
        }

        $history = Cache::get('nova-advanced-command-runner-history', []);
        array_walk($history, function (&$val) {
            $val['time'] = Carbon::createFromTimestamp($val['time'])->diffForHumans();
        });

        $custom_commands = [];

        if(isset($data['custom_commands']) && is_array($data['custom_commands'])){
            foreach ($data['custom_commands'] as $custom_command){
                $custom_commands[$custom_command] = ucfirst($custom_command) .' Command';
            }
        }

        return [
            'commands' => $commands,
            'history' => $history,
            'help' => isset($data['help']) ? $data['help'] : '',
            'heading' => isset($data['navigation_label']) ? $data['navigation_label'] : 'Command Runner',
            'custom_commands' => $custom_commands
        ];
    }

    public function run(Request $request)
    {
        $command = $request->input('command');
        $run = $command['command'];
        foreach ($command['variables'] as $variable ){
            $run = str_replace('{'.$variable['label'].'}', $variable['value'], $run );
        }

        foreach ($command['flags'] as $flag){
            if($flag['selected']){
                $run .= ' '.$flag['flag'];
            }
        }

        $history = Cache::get('nova-advanced-command-runner-history', []);

        $start = microtime(true);
        try {
            $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
            if($command['command_type'] === 'artisan'){
                Artisan::call($run, [], $buffer);
            } else if ($command['command_type'] === 'bash'){
                Process::fromShellCommandline($run, base_path(), null, null, null)
                    ->run(function ($type, $message) use ($buffer){
                        $buffer->writeln($message);
                    });
            } else {
                throw new \Exception('Unknown command type: '.$command['command_type']);
            }
            $result = $buffer->fetch();
            $status = true;
        } catch (\Exception $exception) {
            $result = $exception->getMessage();
            $status = false;
        }
        $duration = microtime(true) - $start;

        if( $run === 'cache:forget nova-advanced-command-runner-history'){
            $history = [
                [
                    'type' => $command['command_type'],
                    'ran_by' => auth()->check() ? auth()->user()->name : '',
                    'run'      => 'Clear Command Run History',
                    'status'   => $status ? 'success' : 'error',
                    'result'   => 'Command run history has been cleared successfully.',
                    'time'     => now()->timestamp,
                    'duration' => round($duration, 4),
                ]
            ];
        } else {
            $history = array_slice($history, 0, config('nova-advanced-command-runner.history', 10) - 1);
            array_unshift($history, [
                'type' => $command['command_type'],
                'ran_by' => auth()->check() ? auth()->user()->name : '',
                'run'      => $run,
                'status'   => $status ? 'success' : 'error',
                'result'   => nl2br($result),
                'time'     => now()->timestamp,
                'duration' => round($duration, 4),
            ]);
        }

        Cache::forever('nova-advanced-command-runner-history', $history);

        array_walk($history, function (&$val) {
            $val['time'] = Carbon::createFromTimestamp($val['time'])->diffForHumans();
        });

        return [ 'status' => $status, 'result' => nl2br($result), 'history' => $history ];
    }
}