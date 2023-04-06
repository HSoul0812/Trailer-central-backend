<div is="collapse-menu">
    @if (count(\Laravel\Nova\Nova::availableResources(request())))
        @foreach($navigation as $group => $resources)
            <h3 data-group="{{ $group }}"
                class="navigation-group-title cursor-pointer flex items-center font-normal dim text-white mb-4 text-base no-underline router-link-exact-active router-link-active">
                @if($group === 'Marketplaces')
                    <i class="fa-solid fa-store mr-4"></i>
                @elseif($group === 'Dealer')
                    <i class="fa-solid fa-briefcase mr-4"></i>
                @elseif($group === 'Factory Feeds')
                    <i class="fa-solid fa-rss mr-4"></i>
                @elseif($group === 'Features')
                    <i class="fa-solid fa-pizza-slice mr-4"></i>
                @elseif($group === 'Integration')
                    <i class="fa-solid fa-tower-cell mr-4"></i>
                @elseif($group === 'Inventory')
                    <i class="fa-solid fa-warehouse mr-4"></i>
                @elseif($group === 'Collector')
                    <i class="fa-solid fa-building-columns mr-4"></i>
                @elseif($group === 'Leads')
                    <i class="fa-solid fa-users mr-4"></i>
                @elseif($group === 'Manufacturer')
                    <i class="fa-solid fa-tractor mr-4"></i>
                @elseif($group === 'Other')
                    <i class="fa-solid fa-building-columns mr-4"></i>
                @elseif($group === 'Parts')
                    <i class="fa-solid fa-gear mr-4"></i>
                @elseif($group === 'Permissions')
                    <i class="fa-solid fa-building-columns mr-4"></i>
                @elseif($group === 'QuickBooks')
                    <i class="fa-solid fa-users mr-4"></i>
                @elseif($group === 'Showroom')
                    <i class="fa-solid fa-truck mr-4"></i>
                @elseif($group === 'Websites')
                    <i class="fa-solid fa-globe mr-4"></i>
                @endif
                <span class="sidebar-label">{{ $group }}</span>
                <span class="toggle-icon"></span>
            </h3>

            <ul data-group="{{ $group }}" class="list-reset mb-4">
                @foreach($resources as $resource)
                    <li class="leading-tight mb-1 ml-2 text-sm">
                        <router-link :to="{
                        name: 'index',
                        params: {
                            resourceName: '{{ $resource::uriKey() }}'
                        }
                    }" class="text-white text-justify no-underline dim" dusk="{{ $resource::uriKey() }}-resource-link">
                            {{ $resource::label() }}
                        </router-link>
                    </li>
                @endforeach
            </ul>
        @endforeach
    @endif
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const headers = document.querySelectorAll('h3[data-group]');
        headers.forEach(header => {
            header.addEventListener('click', function () {
                toggleGroup(header);
            });
        });

        // Find the active link and expand the corresponding group
        const activeLink = document.querySelector('a.router-link-active');
        console.log(activeLink)
        if (activeLink) {
            const activeGroupHeader = activeLink.parentElement.parentElement.previousElementSibling;
            console.log(activeGroupHeader)
            if (activeGroupHeader) {
                toggleGroup(activeGroupHeader);
            }
        }
    });

    function toggleGroup(header) {
        const groupKey = header.getAttribute('data-group');
        const lists = document.querySelectorAll('ul[data-group]');
        const icon = header.querySelector('.toggle-icon');
        console.log(icon)
        lists.forEach(list => {
            if (list.getAttribute('data-group') === groupKey) {
                if (list.style.display === 'none' || list.style.display === '') {
                    list.style.display = 'block';
                    icon.classList.add('open');
                } else {
                    list.style.display = 'none';
                    icon.classList.remove('open');
                }
            } else {
                list.style.display = 'none';
                const otherIcon = list.parentElement.querySelector('.toggle-icon');
                otherIcon.classList.remove('open');
            }
        });
    }
</script>


