!macro customInstall
      SetOutPath $INSTDIR\resources\cti
      CreateShortCut "$SMPROGRAMS\ダイヤス CTI.lnk" "$INSTDIR\resources\cti\CTIService.exe"
      CreateShortCut "$DESKTOP\ダイヤス CTI.lnk" "$INSTDIR\resources\cti\CTIService.exe"
!macroend
