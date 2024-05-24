import DataMigrationBox from './Components/DataMigrationBox.jsx';

let {__} = wp.i18n;

jQuery(function () {

    let messages = {};

    ReactDOM.render(
        <DataMigrationBox nonce={ppmaSyncAuthorSlug.nonce}
                          chunkSize={ppmaSyncAuthorSlug.chunkSize}
                          actionGetInitialData={'get_sync_author_slug_data'}
                          actionMigrationStep={'sync_author_slug'}
                          actionFinishProcess={'finish_sync_author_slug'}
                          buttonLabel={ppmaSyncAuthorSlug.buttonLabel}
                          messageCollectingData={ppmaSyncAuthorSlug.messageCollectingData}
                          messageEndingProcess={ppmaSyncAuthorSlug.messageEndingProcess}
                          messageDone={ppmaSyncAuthorSlug.messageDone}
                          messageWait={ppmaSyncAuthorSlug.messageWait}
                          messageStarting={ppmaSyncAuthorSlug.messageStarting}
                          messageProgress={ppmaSyncAuthorSlug.messageProgress}
        />,
        document.getElementById('publishpress-authors-sync-author-slug')
    );
});

