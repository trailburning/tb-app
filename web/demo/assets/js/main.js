require.config({
	paths: {
		modernizr: 'libs/modernizr.custom.76241',
		underscore: 'libs/underscore-min',
		backbone: 'libs/backbone-min',
    async: 'libs/async',
    piste: 'https://s3.eu-central-1.amazonaws.com/piste.io.twentyfivezero/piste.io.twentyfivezero'
	}
});
// Load our app module and pass it to our definition function
require(['controller/BaseApp'], function(App){
	App.initialize();
})
