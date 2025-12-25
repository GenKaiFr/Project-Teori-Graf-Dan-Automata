@extends('layouts.app')

@section('title', 'Dashboard - Sistem Penjadwalan Rapat')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200 mb-2">Dashboard</h1>
    <p class="text-gray-600 dark:text-gray-400">Sistem Penjadwalan Rapat Cerdas dengan Graf Konflik & Automata</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Rapat</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400">
                <i class="fas fa-calendar-day text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Hari Ini</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['today'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Akan Datang</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['upcoming'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Draft</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['conflicts'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-4">
        @if(auth()->user()->canManageMeetings())
            <a href="{{ route('meetings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>Tambah Rapat
            </a>
        @endif
        <a href="{{ route('calendar') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-calendar mr-2"></i>Lihat Kalender
        </a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('rooms.index') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-door-open mr-2"></i>Kelola Ruangan
            </a>
        @endif
        <a href="{{ route('meetings.graph') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
            <i class="fas fa-project-diagram mr-2"></i>Graf Konflik
        </a>
        @if(auth()->user()->canManageMeetings())
            <a href="{{ route('meetings.bulk') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-tasks mr-2"></i>Bulk Operations
            </a>
        @endif
    </div>
</div>

<!-- Recent Meetings -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Rapat Terbaru</h2>
    @if($recentMeetings->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">Belum ada rapat yang dijadwalkan.</p>
    @else
        <div class="space-y-4">
            @foreach($recentMeetings as $meeting)
            <div class="border-l-4 border-blue-500 dark:border-blue-400 pl-4 py-2">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200">{{ $meeting->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            {{ $meeting->start_time->format('d/m/Y H:i') }} - {{ $meeting->end_time->format('H:i') }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-door-open mr-1"></i>
                            {{ $meeting->room->name }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($meeting->status === 'SCHEDULED') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($meeting->status === 'DRAFT') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($meeting->status === 'ONGOING') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif($meeting->status === 'COMPLETED') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ $meeting->status }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Graph Visualization Modal -->
<div id="graphModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-6xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Visualisasi Graf Konflik</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Garis merah menunjukkan konflik antar rapat</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Scheduled</span>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Draft</span>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Ongoing</span>
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                            <span class="text-gray-600 dark:text-gray-400">Cancelled</span>
                        </div>
                        <button onclick="closeGraphModal()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Graph Controls -->
                <div class="flex justify-between items-center mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <div class="flex items-center space-x-4">
                        <button onclick="resetZoom()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                            <i class="fas fa-search-minus mr-1"></i>Reset Zoom
                        </button>
                        <button onclick="centerGraph()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            <i class="fas fa-crosshairs mr-1"></i>Center
                        </button>
                        <button onclick="togglePhysics()" id="physicsBtn" class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700">
                            <i class="fas fa-pause mr-1"></i>Pause Physics
                        </button>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span id="nodeCount">0</span> rapat, <span id="conflictCount">0</span> konflik
                    </div>
                </div>
                
                <div id="graphContainer" class="w-full h-96 border dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-900"></div>
                
                <!-- Selected Node Info -->
                <div id="nodeInfo" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded hidden">
                    <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">Detail Rapat</h4>
                    <div id="nodeDetails" class="text-sm text-blue-700 dark:text-blue-300"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
let currentSimulation = null;
let currentSvg = null;
let physicsRunning = true;

function showGraphVisualization() {
    document.getElementById('graphModal').classList.remove('hidden');
    loadGraphData();
}

function closeGraphModal() {
    document.getElementById('graphModal').classList.add('hidden');
    if (currentSimulation) {
        currentSimulation.stop();
    }
}

function loadGraphData() {
    fetch('{{ route("meetings.graphData") }}')
        .then(response => response.json())
        .then(data => {
            renderGraph(data);
            updateStats(data);
        })
        .catch(error => {
            console.error('Error loading graph data:', error);
            document.getElementById('graphContainer').innerHTML = 
                '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading data</div>';
        });
}

function updateStats(data) {
    document.getElementById('nodeCount').textContent = data.nodes.length;
    document.getElementById('conflictCount').textContent = data.links.length;
}

function renderGraph(data) {
    const container = document.getElementById('graphContainer');
    container.innerHTML = '';
    
    if (data.nodes.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400"><i class="fas fa-info-circle mr-2"></i>Tidak ada rapat untuk ditampilkan</div>';
        return;
    }

    const width = container.offsetWidth;
    const height = container.offsetHeight;

    // Create SVG with zoom behavior
    const svg = d3.select('#graphContainer')
        .append('svg')
        .attr('width', width)
        .attr('height', height)
        .style('cursor', 'grab');
    
    currentSvg = svg;

    const g = svg.append('g');

    // Add zoom behavior
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });

    svg.call(zoom);

    // Create simulation
    const simulation = d3.forceSimulation(data.nodes)
        .force('link', d3.forceLink(data.links).id(d => d.id).distance(120))
        .force('charge', d3.forceManyBody().strength(-400))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(35));
    
    currentSimulation = simulation;

    // Create arrow markers for directed edges
    svg.append('defs').selectAll('marker')
        .data(['conflict'])
        .enter().append('marker')
        .attr('id', d => d)
        .attr('viewBox', '0 -5 10 10')
        .attr('refX', 25)
        .attr('refY', 0)
        .attr('markerWidth', 6)
        .attr('markerHeight', 6)
        .attr('orient', 'auto')
        .append('path')
        .attr('d', 'M0,-5L10,0L0,5')
        .attr('fill', '#ef4444');

    // Create links with animation
    const link = g.append('g')
        .selectAll('line')
        .data(data.links)
        .enter().append('line')
        .attr('stroke', '#ef4444')
        .attr('stroke-width', 3)
        .attr('stroke-opacity', 0.8)
        .attr('marker-end', 'url(#conflict)')
        .style('stroke-dasharray', '5,5')
        .style('animation', 'dash 1s linear infinite');

    // Create nodes with enhanced styling
    const node = g.append('g')
        .selectAll('g')
        .data(data.nodes)
        .enter().append('g')
        .style('cursor', 'pointer')
        .call(d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended))
        .on('click', (event, d) => showNodeDetails(d))
        .on('mouseover', (event, d) => highlightNode(d, true))
        .on('mouseout', (event, d) => highlightNode(d, false));

    // Add circles to nodes
    node.append('circle')
        .attr('r', 25)
        .attr('fill', d => getNodeColor(d.status))
        .attr('stroke', '#fff')
        .attr('stroke-width', 3)
        .style('filter', 'drop-shadow(2px 2px 4px rgba(0,0,0,0.3))');

    // Add status icons to nodes
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 2)
        .attr('font-size', 14)
        .attr('fill', 'white')
        .attr('font-weight', 'bold')
        .text(d => getStatusIcon(d.status));

    // Add labels below nodes
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 45)
        .attr('font-size', 11)
        .attr('font-weight', 'bold')
        .attr('fill', document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151')
        .text(d => d.title.length > 15 ? d.title.substring(0, 15) + '...' : d.title);

    // Add time labels
    node.append('text')
        .attr('text-anchor', 'middle')
        .attr('dy', 58)
        .attr('font-size', 9)
        .attr('fill', document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280')
        .text(d => d.start_time);

    // Animation for nodes
    node.style('opacity', 0)
        .transition()
        .duration(500)
        .delay((d, i) => i * 50)
        .style('opacity', 1);

    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

        node.attr('transform', d => `translate(${d.x},${d.y})`);
    });

    // Store zoom for reset function
    svg.zoom = zoom;
    svg.g = g;

    function dragstarted(event, d) {
        if (!event.active && physicsRunning) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
        svg.style('cursor', 'grabbing');
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active && physicsRunning) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
        svg.style('cursor', 'grab');
    }
}

function getNodeColor(status) {
    switch(status) {
        case 'SCHEDULED': return '#10b981';
        case 'DRAFT': return '#f59e0b';
        case 'ONGOING': return '#3b82f6';
        case 'COMPLETED': return '#6b7280';
        case 'CANCELLED': return '#ef4444';
        default: return '#8b5cf6';
    }
}

function getStatusIcon(status) {
    switch(status) {
        case 'SCHEDULED': return '✓';
        case 'DRAFT': return '📝';
        case 'ONGOING': return '▶';
        case 'COMPLETED': return '✅';
        case 'CANCELLED': return '❌';
        default: return '?';
    }
}

function highlightNode(nodeData, highlight) {
    if (!currentSvg) return;
    
    const nodes = currentSvg.selectAll('g g');
    const links = currentSvg.selectAll('line');
    
    if (highlight) {
        // Dim all nodes and links
        nodes.style('opacity', 0.3);
        links.style('opacity', 0.1);
        
        // Highlight selected node and connected nodes
        nodes.filter(d => d.id === nodeData.id)
            .style('opacity', 1)
            .select('circle')
            .attr('r', 30)
            .style('filter', 'drop-shadow(0 0 10px rgba(59, 130, 246, 0.8))');
        
        // Highlight connected links
        links.filter(d => d.source.id === nodeData.id || d.target.id === nodeData.id)
            .style('opacity', 1)
            .attr('stroke-width', 4);
        
        // Highlight connected nodes
        const connectedNodeIds = new Set();
        links.filter(d => d.source.id === nodeData.id || d.target.id === nodeData.id)
            .each(d => {
                connectedNodeIds.add(d.source.id);
                connectedNodeIds.add(d.target.id);
            });
        
        nodes.filter(d => connectedNodeIds.has(d.id))
            .style('opacity', 0.8);
    } else {
        // Reset all styles
        nodes.style('opacity', 1);
        links.style('opacity', 0.8).attr('stroke-width', 3);
        nodes.select('circle')
            .attr('r', 25)
            .style('filter', 'drop-shadow(2px 2px 4px rgba(0,0,0,0.3))');
    }
}

function showNodeDetails(nodeData) {
    const nodeInfo = document.getElementById('nodeInfo');
    const nodeDetails = document.getElementById('nodeDetails');
    
    nodeDetails.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div><strong>Judul:</strong> ${nodeData.title}</div>
            <div><strong>Status:</strong> ${nodeData.status}</div>
            <div><strong>Ruangan:</strong> ${nodeData.room_name}</div>
            <div><strong>Waktu:</strong> ${nodeData.start_time}</div>
            <div><strong>Durasi:</strong> ${nodeData.duration || 'N/A'}</div>
            <div><strong>Peserta:</strong> ${nodeData.participants_count || 0} orang</div>
        </div>
        ${nodeData.description ? `<div class="mt-2"><strong>Deskripsi:</strong> ${nodeData.description}</div>` : ''}
    `;
    
    nodeInfo.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        nodeInfo.classList.add('hidden');
    }, 5000);
}

function resetZoom() {
    if (currentSvg && currentSvg.zoom) {
        currentSvg.transition().duration(750).call(
            currentSvg.zoom.transform,
            d3.zoomIdentity
        );
    }
}

function centerGraph() {
    if (currentSimulation) {
        const container = document.getElementById('graphContainer');
        const width = container.offsetWidth;
        const height = container.offsetHeight;
        
        currentSimulation.force('center', d3.forceCenter(width / 2, height / 2));
        currentSimulation.alpha(0.3).restart();
    }
}

function togglePhysics() {
    const btn = document.getElementById('physicsBtn');
    
    if (physicsRunning) {
        if (currentSimulation) currentSimulation.stop();
        btn.innerHTML = '<i class="fas fa-play mr-1"></i>Resume Physics';
        btn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        physicsRunning = false;
    } else {
        if (currentSimulation) currentSimulation.restart();
        btn.innerHTML = '<i class="fas fa-pause mr-1"></i>Pause Physics';
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-purple-600', 'hover:bg-purple-700');
        physicsRunning = true;
    }
}

// Add CSS animation for dashed lines
const style = document.createElement('style');
style.textContent = `
    @keyframes dash {
        to {
            stroke-dashoffset: -10;
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush
@endsection