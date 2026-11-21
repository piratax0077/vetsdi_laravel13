<!DOCTYPE html>
<html>
  <head>
    <script src='https://8x8.vc/{{ config('services.jitsi.app_id') }}/external_api.js' async></script>
    <style>html, body, #jaas-container { height: 100%; }</style>
    <script type="text/javascript">
      window.onload = () => {
        const api = new JitsiMeetExternalAPI("8x8.vc", {
			roomName: @js($id . "/" . $nombre),
            jwt: @js($token_invitado),
			parentNode: document.querySelector('#jaas-container'),
                        // Make sure to include a JWT if you intend to record,
                        // make outbound calls or use any other premium features!
            jwt: "{{ $token_invitado }}",
            configOverwrite: {
                startWithAudioMuted: false,
                enableNoisyMicDetection: true,
                // toolbarButtons: ['hangup', 'microphone', 'camera','chat'],
                prejoinPageEnabled: true,
                // Transcription options.
                transcription: {
                    // Whether the feature should be enabled or not.
                    enabled: false,

                    // Translation languages.
                    // Available languages can be found in
                    // ./src/react/features/transcribing/translation-languages.json.
                    translationLanguages: ['en', 'es', 'fr', 'ro'],

                    // Important languages to show on the top of the language list.
                    translationLanguagesHead: ['en'],

                    // If true transcriber will use the application language.
                    // The application language is either explicitly set by participants in their settings or automatically
                    // detected based on the environment, e.g. if the app is opened in a chrome instance which
                    // is using french as its default language then transcriptions for that participant will be in french.
                    // Defaults to true.
                    useAppLanguage: true,

                    // Transcriber language. This settings will only work if "useAppLanguage"
                    // is explicitly set to false.
                    // Available languages can be found in
                    // ./src/react/features/transcribing/transcriber-langs.json.
                    preferredLanguage: 'en-US',

                    // Enables automatic turning on transcribing when recording is started
                    autoTranscribeOnRecord: false,
                },
             },
            interfaceConfigOverwrite: {
                DISABLE_DOMINANT_SPEAKER_INDICATOR: true,
                AUDIO_LEVEL_PRIMARY_COLOR: 'rgba(255,255,255,0.4)',
                AUDIO_LEVEL_SECONDARY_COLOR: 'rgba(255,255,255,0.2)',
            },
            lang: 'es'
        });
      }
    </script>
  </head>
  <body><div id="jaas-container" ></body>
</html>
