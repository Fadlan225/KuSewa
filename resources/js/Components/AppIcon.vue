<script setup>
import { computed } from 'vue';
import { 
  ArrowDown, ArrowDownWideNarrow, ArrowLeft, ArrowRight, TrendingUp, Upload, RefreshCw, Ban, Bed, Bell, BookOpen, Grid3X3, Briefcase, Paintbrush, Building, Landmark, ShieldCheck, Calculator, Calendar, CalendarCheck, Camera, BarChart, LineChart, PieChart, Check, CheckCircle, CheckCheck, ChevronDown, ChevronLeft, ChevronRight, Circle, AlertCircle, Info, Loader2, HelpCircle, XCircle, Building2, ClipboardList, Clock, History, CloudUpload, MessageSquareMore, MessageCircle, Copy, CreditCard, DoorOpen, Download, MoreVertical, Mail, AlertTriangle, Eye, Facebook, File, FileSignature, Image, Receipt, FileText, FileEdit, Filter, Flame, Flag, FolderOpen, FolderPlus, Settings, Chrome, Headset, Heart, Hourglass, Home, Lock, IdCard, Images, Inbox, Instagram, Layers, Link, List, Locate, MapPin, Search, Map, Medal, Minus, Send, Paperclip, Pause, Pen, Edit, Percent, Phone, Volume2, Play, Plus, Power, Reply, LogOut, RotateCw, Ruler, Coins, Save, Share2, Shield, Sliders, Star, Trash2, Twitter, User, Users, Wallet, X, Globe,
  ArrowUp, ArrowUpWideNarrow, Zap, ChevronUp, CircleDot, PlayCircle, EyeOff, ArrowRightLeft, QrCode, StarHalf, Tag, UserPlus, UserMinus
} from 'lucide-vue-next';

const props = defineProps({
  iconClass: { type: [String, Array, Object], default: '' }
});

const iconsMap = {
  "fa-arrow-down": ArrowDown,
  "fa-arrow-down-short-wide": ArrowDownWideNarrow,
  "fa-arrow-down-wide-short": ArrowDownWideNarrow,
  "fa-arrow-left": ArrowLeft,
  "fa-arrow-right": ArrowRight,
  "fa-arrow-right-from-bracket": LogOut,
  "fa-arrow-trend-up": TrendingUp,
  "fa-arrow-up": ArrowUp,
  "fa-arrow-up-from-bracket": Upload,
  "fa-arrow-up-wide-short": ArrowUpWideNarrow,
  "fa-arrows-rotate": RefreshCw,
  "fa-ban": Ban,
  "fa-bed": Bed,
  "fa-bell": Bell,
  "fa-bolt": Zap,
  "fa-book-open": BookOpen,
  "fa-border-all": Grid3X3,
  "fa-briefcase": Briefcase,
  "fa-broom": Paintbrush,
  "fa-building": Building,
  "fa-building-columns": Landmark,
  "fa-building-shield": ShieldCheck,
  "fa-calculator": Calculator,
  "fa-calendar": Calendar,
  "fa-calendar-check": CalendarCheck,
  "fa-camera": Camera,
  "fa-chart-column": BarChart,
  "fa-chart-line": LineChart,
  "fa-chart-pie": PieChart,
  "fa-check": Check,
  "fa-check-circle": CheckCircle,
  "fa-check-double": CheckCheck,
  "fa-chevron-down": ChevronDown,
  "fa-chevron-left": ChevronLeft,
  "fa-chevron-right": ChevronRight,
  "fa-chevron-up": ChevronUp,
  "fa-circle": Circle,
  "fa-circle-check": CheckCircle,
  "fa-circle-dot": CircleDot,
  "fa-circle-exclamation": AlertCircle,
  "fa-circle-info": Info,
  "fa-circle-notch": Loader2,
  "fa-circle-play": PlayCircle,
  "fa-circle-question": HelpCircle,
  "fa-circle-xmark": XCircle,
  "fa-city": Building2,
  "fa-clipboard-list": ClipboardList,
  "fa-clock": Clock,
  "fa-clock-rotate-left": History,
  "fa-cloud-arrow-up": CloudUpload,
  "fa-comment-dots": MessageSquareMore,
  "fa-comments": MessageCircle,
  "fa-copy": Copy,
  "fa-credit-card": CreditCard,
  "fa-door-open": DoorOpen,
  "fa-download": Download,
  "fa-ellipsis-vertical": MoreVertical,
  "fa-envelope": Mail,
  "fa-exclamation-triangle": AlertTriangle,
  "fa-eye": Eye,
  "fa-eye-slash": EyeOff,
  "fa-facebook-f": Facebook,
  "fa-file": File,
  "fa-file-contract": FileSignature,
  "fa-file-image": Image,
  "fa-file-invoice": Receipt,
  "fa-file-invoice-dollar": Receipt,
  "fa-file-lines": FileText,
  "fa-file-pen": FileEdit,
  "fa-filter": Filter,
  "fa-fire": Flame,
  "fa-flag-checkered": Flag,
  "fa-folder-open": FolderOpen,
  "fa-folder-plus": FolderPlus,
  "fa-gear": Settings,
  "fa-globe": Globe,
  "fa-google": Chrome,
  "fa-headset": Headset,
  "fa-heart": Heart,
  "fa-hourglass-half": Hourglass,
  "fa-house": Home,
  "fa-house-chimney": Home,
  "fa-house-lock": Lock,
  "fa-id-card": IdCard,
  "fa-image": Image,
  "fa-images": Images,
  "fa-inbox": Inbox,
  "fa-instagram": Instagram,
  "fa-landmark": Landmark,
  "fa-layer-group": Layers,
  "fa-link": Link,
  "fa-list": List,
  "fa-location-crosshairs": Locate,
  "fa-location-dot": MapPin,
  "fa-lock": Lock,
  "fa-magnifying-glass": Search,
  "fa-map-location-dot": Map,
  "fa-medal": Medal,
  "fa-minus": Minus,
  "fa-money-bill-transfer": ArrowRightLeft,
  "fa-paper-plane": Send,
  "fa-paperclip": Paperclip,
  "fa-pause": Pause,
  "fa-pen": Pen,
  "fa-pen-to-square": Edit,
  "fa-percent": Percent,
  "fa-phone": Phone,
  "fa-phone-volume": Volume2,
  "fa-play": Play,
  "fa-plus": Plus,
  "fa-power-off": Power,
  "fa-qrcode": QrCode,
  "fa-question": HelpCircle,
  "fa-receipt": Receipt,
  "fa-reply": Reply,
  "fa-right-from-bracket": LogOut,
  "fa-rotate-right": RotateCw,
  "fa-ruler-combined": Ruler,
  "fa-rupiah-sign": Coins,
  "fa-save": Save,
  "fa-search": Search,
  "fa-share-nodes": Share2,
  "fa-shield-halved": Shield,
  "fa-sliders": Sliders,
  "fa-spinner": Loader2,
  "fa-star": Star,
  "fa-star-half-stroke": StarHalf,
  "fa-tag": Tag,
  "fa-times-circle": XCircle,
  "fa-trash": Trash2,
  "fa-trash-can": Trash2,
  "fa-triangle-exclamation": AlertTriangle,
  "fa-twitter": Twitter,
  "fa-user": User,
  "fa-user-group": Users,
  "fa-user-plus": UserPlus,
  "fa-user-slash": UserMinus,
  "fa-users": Users,
  "fa-wallet": Wallet,
  "fa-whatsapp": MessageCircle,
  "fa-xmark": X,
  "fa-xmark-circle": XCircle
};

const parsedClasses = computed(() => {
  let str = '';
  if (typeof props.iconClass === 'string') {
    str = props.iconClass;
  } else if (Array.isArray(props.iconClass)) {
    str = props.iconClass.join(' ');
  } else if (typeof props.iconClass === 'object' && props.iconClass !== null) {
    str = Object.keys(props.iconClass).filter(k => props.iconClass[k]).join(' ');
  }
  return str.split(/[\s,]+/);
});

const lucideComponent = computed(() => {
  for (const c of parsedClasses.value) {
    if (iconsMap[c]) return iconsMap[c];
  }
  return HelpCircle; // Fallback
});

const remainingClasses = computed(() => {
  return parsedClasses.value
    .filter(c => !iconsMap[c] && !['fa-solid','fa-regular','fa-brands','fas','far','fab','fa-fw','fa-spin','fa-pulse'].includes(c))
    .concat(parsedClasses.value.includes('fa-spin') || parsedClasses.value.includes('fa-pulse') ? ['animate-spin'] : [])
    .join(' ');
});
</script>
<template>
  <component :is="lucideComponent" :class="remainingClasses" />
</template>
