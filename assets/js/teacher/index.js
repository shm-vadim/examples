import '../app';
import defaultDefinitions from '../DataTables/defaultDefinitions';
import createLanguageSettings from '../DataTables/createLanguageSettings';

defaultDefinitions(".table-teachers", {
    searching: true,
    language: createLanguageSettings({from: "учителей"}),
});
