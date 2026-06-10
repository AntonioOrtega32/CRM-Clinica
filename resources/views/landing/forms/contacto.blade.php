<section id="contacto" class="py-20 px-6 bg-[#ffffff]">
    <div class="max-w-3xl mx-auto">
        @if(request()->is('clinicas/santafe'))
        <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-4 leading-relaxed" style="color: #1C6C73; line-height: 1.4;">
            Lorem ipsum dolor sit amet consectetur adipiscing elit.
        </h2>
        <p class="text-center lg:text-[18px] text-[16px] mb-12 ">
          Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. 
        </p>
        @elseif(request()->is(patterns: 'clinicas/pedregal'))
        <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-4 leading-relaxed" style="color: #1C6C73; line-height: 1.4;">
            Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas.
        </h2>
        <p class="text-center lg:text-[18px] text-[16px] mb-12 ">
         Ut hendrerit semper vel class aptent taciti sociosqu.
        </p>
        @elseif(request()->is('clinicas/queretaro'))
        <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-4 leading-relaxed" style="color: #1C6C73; line-height: 1.4;">
            Ut hendrerit semper vel class aptent taciti sociosqu.
        </h2>
        <p class="text-center lg:text-[18px] text-[16px] mb-12 ">
           Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas.
        </p>
        @else
        <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-4 leading-relaxed" style="color: #1C6C73; line-height: 1.4;">
           Lorem ipsum dolor <br class="hidden md:block"> it amet consectetur adipiscing elit. 
        </h2>
        <p class="text-center lg:text-[18px] text-[16px] mb-12 ">
           Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. 
        </p>
        @endif

        <!-- Contenedor para aplicar tus estilos -->
        <div class="bg-white p-10 rounded-3xl shadow-[8px_8px_20px_rgba(0,0,0,0.1),-8px_-8px_20px_rgba(255,255,255,0.7)]">

            <form class="max-w-md mx-auto">
            <div class="relative z-0 w-full mb-5 group">
                <input type="email" name="floating_email" id="floating_email" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                <label for="floating_email" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Correo Electronico</label>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <input type="password" name="floating_password" id="floating_password" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                <label for="floating_password" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Contraseña</label>
            </div>
            <div class="relative z-0 w-full mb-5 group">
                <input type="password" name="repeat_password" id="floating_repeat_password" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                <label for="floating_repeat_password" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Confirmar contraseña</label>
            </div>
            <div class="grid md:grid-cols-2 md:gap-6">
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" name="floating_first_name" id="floating_first_name" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                    <label for="floating_first_name" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Nombre(s)</label>
                </div>
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" name="floating_last_name" id="floating_last_name" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                    <label for="floating_last_name" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Apellidos</label>
                </div>
            </div>
            <div class="grid md:grid-cols-2 md:gap-6">
                <div class="relative z-0 w-full mb-5 group">
                    <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="floating_phone" id="floating_phone" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                    <label for="floating_phone" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Telefono</label>
                </div>
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" name="floating_company" id="floating_company" class="block py-2.5 px-0 w-full text-sm text-heading bg-transparent border-0 border-b-2 border-default-medium appearance-none focus:outline-none focus:ring-0 focus:border-brand peer" placeholder=" " required />
                    <label for="floating_company" class="absolute text-sm text-body duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-fg-brand peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">¿Dónde nos conoció?</label>
                </div>
            </div>
            <button type="submit" class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">Submit</button>
            </form>

            <!-- Formulario HubSpot (Fuera de uso en esta demo)
            <div id="hubspot-form-container" class="hs-form-frame w-full"
                data-region="na1"
                data-form-id="8bcdf1d8-0c76-40a6-99bd-58365cd4b46e"
                data-portal-id="50066249">
                <p>Cargando formulario...</p>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const target = document.querySelector('#hubspot-form-container');

                    const observer = new IntersectionObserver(async (entries, obs) => {
                        if (entries[0].isIntersecting) {
                            obs.unobserve(target);

                            // Cargar script de HubSpot dinámicamente
                            const script = document.createElement('script');
                            script.src = "https://js.hsforms.net/forms/embed/50066249.js";
                            script.defer = true;
                            document.body.appendChild(script);

                            script.onload = () => {
                                hbspt.forms.create({
                                    region: target.dataset.region,
                                    portalId: target.dataset.portalId,
                                    formId: target.dataset.formId,
                                    target: "#hubspot-form-container"
                                });
                            };
                        }
                    });

                    observer.observe(target);
                });
            </script>
        -->


        </div>
    </div>
</section>