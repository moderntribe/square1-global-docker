#!/usr/bin/env bash

_sq1()
{
    local cur script coms opts com
    COMPREPLY=()
    _get_comp_words_by_ref -n : cur words

    # for an alias, get the real script behind it
    if [[ $(type -t ${words[0]}) == "alias" ]]; then
        script=$(alias ${words[0]} | sed -E "s/alias ${words[0]}='(.*)'/\1/")
    else
        script=${words[0]}
    fi

    # lookup for command
    for word in ${words[@]:1}; do
        if [[ $word != -* ]]; then
            com=$word
            break
        fi
    done

    # completing for an option
    if [[ ${cur} == --* ]] ; then
        opts="--help --quiet --verbose --version --ansi --no-ansi --no-interaction"

        case "$com" in

            composer)
            opts="${opts} "
            ;;

            create)
            opts="${opts} "
            ;;

            help)
            opts="${opts} --format --raw"
            ;;

            list)
            opts="${opts} --raw --format"
            ;;

            restart)
            opts="${opts} "
            ;;

            shell)
            opts="${opts} "
            ;;

            start)
            opts="${opts} "
            ;;

            stop)
            opts="${opts} "
            ;;

            wp)
            opts="${opts} "
            ;;

            wpx)
            opts="${opts} "
            ;;

            global:cert)
            opts="${opts} "
            ;;

            global:myadmin)
            opts="${opts} "
            ;;

            global:restart)
            opts="${opts} "
            ;;

            global:start)
            opts="${opts} "
            ;;

            global:status)
            opts="${opts} "
            ;;

            global:stop)
            opts="${opts} "
            ;;

            global:stop-all)
            opts="${opts} "
            ;;

        esac

        COMPREPLY=($(compgen -W "${opts}" -- ${cur}))
        __ltrim_colon_completions "$cur"

        return 0;
    fi

    # completing for a command
    if [[ $cur == $com ]]; then
        coms="composer create help list restart shell start stop wp wpx global:cert global:myadmin global:restart global:start global:status global:stop global:stop-all"

        COMPREPLY=($(compgen -W "${coms}" -- ${cur}))
        __ltrim_colon_completions "$cur"

        return 0
    fi
}

complete -o default -F _sq1 sq1